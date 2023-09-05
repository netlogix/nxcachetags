<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Hooks;

use Netlogix\Nxcachetags\Hooks\DataHandler;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerTest extends FunctionalTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');

    }

    /**
     * @test
     *
     * @return void
     */
    public function itDoesNotAddRootLineTagsIfRequestTypeIsNotRelevant()
    {
        $dataHandlerMock = $this->getMockBuilder(\TYPO3\CMS\Core\DataHandling\DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataHandler|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(DataHandler::class, ['isRelevantRequestType']);

        self::assertEmpty($subject->_get('tagsToFlush'));

        $subject->method('isRelevantRequestType')->willReturn(false);

        $subject->processCmdmap_preProcess('move', 'pages', 6, [], $dataHandlerMock);

        self::assertEmpty($subject->_get('tagsToFlush'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function itAddsRootLineTags()
    {
        $dataHandlerMock = $this->getMockBuilder(\TYPO3\CMS\Core\DataHandling\DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();


        /** @var DataHandler|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(DataHandler::class, ['isRelevantRequestType']);

        self::assertEmpty($subject->_get('tagsToFlush'));

        $subject->method('isRelevantRequestType')->willReturn(true);

        $subject->processCmdmap_preProcess('move', 'pages', 6, [], $dataHandlerMock);

        self::assertNotEmpty($subject->_get('tagsToFlush'));
        self::assertEquals(
            ['rootline_6', 'rootline_5', 'rootline_1', 'rootline_0'],
            $subject->_get('tagsToFlush')
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function itDoesNotTryToFlushTagsIfRequestTypeIsNotRelevant()
    {
        $mockCacheManager = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flushCachesInGroupByTag'])
            ->getMock();
        $mockCacheManager->expects(self::never())->method('flushCachesInGroupByTag');
        GeneralUtility::setSingletonInstance(CacheManager::class, $mockCacheManager);

        $dataHandlerMock = $this->getMockBuilder(\TYPO3\CMS\Core\DataHandling\DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataHandler|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(DataHandler::class, ['isRelevantRequestType']);
        $subject->method('isRelevantRequestType')->willReturn(false);

        $subject->clearCachePostProc(['table' => 'pages', 'uid' => 6], $dataHandlerMock);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itTriesToFlushTagsIfRequestTypeIsRelevant()
    {
        $fakeTranslationParent = rand(10, 100);

        $mockCacheManager = $this->getMockBuilder(CacheManager::class)
            ->setConstructorArgs([true])
            ->onlyMethods(['flushCachesInGroupByTag'])
            ->getMock();
        $mockCacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);

        $mockCacheManager->expects($this->exactly(5))->method('flushCachesInGroupByTag')
            ->with(...$this->consecutiveParams(
                ['pages','pages_' . $fakeTranslationParent],
                ['pages','rootline_6'],
                ['pages','rootline_5'],
                ['pages','rootline_1'],
                ['pages','rootline_0'],
            ));
        GeneralUtility::setSingletonInstance(CacheManager::class, $mockCacheManager);

        $dataHandlerMock = $this->getMockBuilder(\TYPO3\CMS\Core\DataHandling\DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        // fakse translation data
        $dataHandlerMock->datamap['pages']['6']['l10n_parent'] = $fakeTranslationParent;

        /** @var DataHandler|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(DataHandler::class, ['isRelevantRequestType']);
        $subject->method('isRelevantRequestType')->willReturn(true);

        $subject->clearCachePostProc(['table' => 'pages', 'uid' => 6], $dataHandlerMock);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itSetsTagsEmptyAfterFlushing()
    {

        $dataHandlerMock = $this->getMockBuilder(\TYPO3\CMS\Core\DataHandling\DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataHandler|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(DataHandler::class, ['isRelevantRequestType']);
        $subject->method('isRelevantRequestType')->willReturn(true);

        $subject->clearCachePostProc(['table' => 'pages', 'uid' => 6], $dataHandlerMock);

        self::assertEmpty($subject->_get('tagsToFlush'));
    }

    // @see: https://gist.github.com/ziadoz/370fe63e24f31fd1eb989e7477b9a472
    public function consecutiveParams(array ...$args): array
    {
        $callbacks = [];
        $count = count(max($args));

        for ($index = 0; $index < $count; $index++) {
            $returns = [];

            foreach ($args as $arg) {
                if (! is_array($arg)) {
                    throw new \InvalidArgumentException('Every array must be a list');
                }

                if (! isset($arg[$index])) {
                    throw new \InvalidArgumentException(sprintf('Every array must contain %d parameters', $count));
                }

                $returns[] = $arg[$index];
            }

            $callbacks[] = $this->callback(new class ($returns) {
                private array $returns;
                public function __construct(array $returns)
                {
                    $this->returns = $returns;
                }

                public function __invoke(mixed $actual): bool
                {
                    if (count($this->returns) === 0) {
                        return true;
                    }

                    return $actual === array_shift($this->returns);
                }
            });
        }

        return $callbacks;
    }
}

<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Service;

use Netlogix\Nxcachetags\Domain\Model\Category;
use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheTagServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     *
     * @return void
     */
    public function itInitializesObjectIdentificationHelpers()
    {
        /** @var CacheTagService|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(CacheTagService::class, null);

        $subject->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $subject->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));

        self::assertEmpty($subject->_get('objectIdentificationHelpers'));

        $subject->initializeObject();

        self::assertNotEmpty($subject->_get('objectIdentificationHelpers'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function itInitializesCacheIdentifierDefaults()
    {
        /** @var CacheTagService|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(CacheTagService::class, null);

        $subject->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $subject->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));

        self::assertEmpty($subject->_get('cacheIdentifierDefaults'));

        $subject->initializeObject();

        self::assertNotEmpty($subject->_get('cacheIdentifierDefaults'));
    }

    /**
     * @test
     * @dataProvider cacheTagForObjectDataProvider
     * @return void
     */
    public function itCanIdentifyCacheTagForObject($object, array $expectedResult)
    {
        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $res = $subject->identifyCacheTagForObject($object);

        self::assertEquals($expectedResult, $res);
    }

    /**
     * @test
     * @return void
     */
    public function itReturnsEmptyCacheTagsForUnknownObject()
    {
        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $res = $subject->identifyCacheTagForObject(new \stdClass());

        self::assertEmpty($res);
    }

    /**
     * @return array
     */
    public static function cacheTagForObjectDataProvider(): array
    {
        $data = [];

        $data['TCA Record'] = ['page_123', ['page_123']];

        $catUid = rand(1, 99999);
        $category = new Category();
        $category->_setProperty('uid', $catUid);
        $data['DomainObject'] = [$category, ['sys_category_' . $catUid]];


        return $data;
    }

    /**
     * @test
     * @dataProvider internalCacheTagsDataProvider
     * @return void
     */
    public function itCanCreateCacheTags($params, array $expectedOutput)
    {
        /** @var CacheTagService|MockObject|AccessibleMockObjectInterface $subject */
        $subject = $this->getAccessibleMock(CacheTagService::class, null);

        $subject->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $subject->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));
        $subject->initializeObject();

        $res = $subject->_call('createCacheTagsInternal', $params);

        self::assertEquals($expectedOutput, $res);
    }

    public static function internalCacheTagsDataProvider(): array
    {

        $data = [
            'null' => [null, []],
            'boolean false' => [false, [false]],
            'boolean true' => [true, [true]],

        ];

        $randString = uniqid('string_');
        $data['string'] = [$randString, [$randString]];

        $randInt = rand(1, 99999);
        $data['integer'] = [$randInt, [$randInt]];

        $randFloat = (float)rand(1, 99999);
        $data['float'] = [$randFloat, [$randFloat]];


        $catUid = rand(1, 99999);
        $category = new Category();
        $category->_setProperty('uid', $catUid);
        $data['DomainObject'] = [$category, ['sys_category_' . $catUid]];

        $in = $out = [];

        foreach ($data as $datum) {
            if (empty($datum[1])) {
                continue;
            }

            $in[] = $datum[0];
            $out = array_merge($out, $datum[1]);
        }

        $data['array data'] = [$in, $out];

        return $data;
    }


    /**
     * @test
     *
     * @return void
     */
    public function itUsesDefaultForEnvironmentLifetime() {
        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $subject->openEnvironment();

        $res = $subject->getEnvironmentLifetime();

        self::assertEquals(0, $res);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itCanIncrementEnvironmentLifetime() {
        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $subject->openEnvironment();

        $lifetime = rand(1, 999);

        $subject->decreaseEnvironmentLifetime($lifetime);

        $res = $subject->getEnvironmentLifetime();

        self::assertEquals($lifetime, $res);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itDoesNotAllowToDecrementEnvironmentLifetimeAboveCurrentValue() {
        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $subject->openEnvironment();

        $lifetime = rand(1, 999);

        $subject->decreaseEnvironmentLifetime($lifetime);
        $subject->decreaseEnvironmentLifetime($lifetime + rand(1,99));

        $res = $subject->getEnvironmentLifetime();

        self::assertEquals($lifetime, $res);
    }

    /**
     * @test
     * @return void
     */
    public function flushesCachesByTag() {
        $mockCacheManager = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flushCachesByTag'])
            ->getMock();

        $tag = uniqid('tag_');

        $mockCacheManager->expects(self::once())->method('flushCachesByTag')->with($tag);

        $subject = GeneralUtility::makeInstance(CacheTagService::class);
        $subject->injectCacheManager($mockCacheManager);

        $subject->flushCachesByTag($tag);
    }

    /**
     * @test
     * @return void
     */
    public function itAddsCacheTagsToTSFE() {
        $tag = uniqid('tag_');

        $subject = $this->getMockBuilder(CacheTagService::class)
            ->onlyMethods(['getTyposcriptFrontendController'])
            ->getMock();

        $mockTSFE = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addCacheTags'])
            ->getMock();
        $mockTSFE->expects(self::once())->method('addCacheTags')->with([$tag]);

        $subject->method('getTyposcriptFrontendController')->willReturn($mockTSFE);

        $subject->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $subject->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));
        $subject->initializeObject();

        $subject->addPageCacheTags($tag);
    }

    /**
     * @test
     * @return void
     */
    public function itWillRemoveTableTagsFromLifetimeSources() {
        $unknownTag = uniqid('tx_') . '_' . rand(1, 999);
        $knownTag = 'pages_' . rand(1, 999);

        $subject = GeneralUtility::makeInstance(CacheTagService::class);

        $res = $subject->findTableCacheTagsForLifetimeSources(['pages', uniqid('tx_'), 'tt_content'], [$unknownTag, $knownTag]);

        self::assertEquals(['tt_content' => 'tt_content'], $res);
    }
}
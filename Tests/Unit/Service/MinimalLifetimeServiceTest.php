<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\Service;

use Netlogix\Nxcachetags\Service\MinimalLifetimeService;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class MinimalLifetimeServiceTest extends UnitTestCase
{

    /**
     * @test
     * @return void
     */
    public function itReturnsLifetimeForRecordIfTableIsNotInTCA() {
        // this is a unit test, there is no TCA to worry about

        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, ['dummy']);
        $lifetime = time() + rand(1, 999999);

        $res = $subject->_call('findMinimalLifetimeForRecord', $lifetime, uniqid('table_'), rand(1,999));

        self::assertEquals($lifetime, $res);
    }
    /**
     * @test
     * @return void
     */
    public function itReturnsLifetimeForTableIfTableIsNotInTCA() {
        // this is a unit test, there is no TCA to worry about

        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, ['dummy']);
        $lifetime = time() + rand(1, 999999);

        $res = $subject->_call('findMinimalLifetimeForTable', $lifetime, uniqid('table_'), []);

        self::assertEquals($lifetime, $res);
    }

    /**
     * @test
     * @dataProvider storagePidConfigurationDataProvider
     * @return void
     */
    public function itCanGetStoragePidsFromConfig(array $config, array $lifetimeSource, array $expectedResult)
    {
        $mockConfigurationManager = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfiguration'])
            ->getMock();

        $mockConfigurationManager->method('getConfiguration')->willReturn($config);

        /** @var MockObject|AccessibleMockObjectInterface|MinimalLifetimeService $subject */
        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, ['dummy']);
        $subject->injectConfigurationManager($mockConfigurationManager);

        $res = $subject->_call('getStoragePids', $lifetimeSource);

        self::assertEquals($res, $expectedResult);
    }

    public function storagePidConfigurationDataProvider(): array
    {
        return [
            'empty configuration' => [[], [], []],
            'table with lifetime' => [['persistence' => ['storagePid' => 999]], [], [999]],
            'table with lifetime, but is ignored' => [
                [
                    'persistence' => [
                        'storagePid' => 999,
                        'noStoragePidForCacheLifetime' => ['pages']
                    ],

                ],
                ['pages'],
                []
            ],


        ];
    }

}

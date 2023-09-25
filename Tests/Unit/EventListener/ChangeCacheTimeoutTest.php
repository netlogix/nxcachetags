<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\EventListener;

use Netlogix\Nxcachetags\EventListener\ChangeCacheTimeout;
use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ChangeCacheTimeoutTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function itChangesCacheTimeoutIfEnvironmentLifetimeIsNotZero(): void
    {
        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );
        if ($typo3VersionNumber < 12000000) {
            self::markTestSkipped('ModifyCacheLifetimeForPageEvent doesn\'t exist in TYPO3 11');
        }
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheTagServiceMock->method('getEnvironmentLifetime')->willReturn(100);
        $event = new \TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent(
            200,
            123,
            [],
            [],
            new Context([])
        );

        $subject = new ChangeCacheTimeout($cacheTagServiceMock);

        $subject->__invoke($event);

        $this->assertEquals(100, $event->getCacheLifetime());
    }

    /**
     * @test
     * @return void
     */

    public function itDoesNotChangeCacheTimeoutIfEnvironmentLifetimeIsZero(): void
    {
        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );
        if ($typo3VersionNumber < 12000000) {
            self::markTestSkipped('ModifyCacheLifetimeForPageEvent doesn\'t exist in TYPO3 11');
        }
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheTagServiceMock->method('getEnvironmentLifetime')->willReturn(0);

        $event = new \TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent(
            200,
            123,
            [],
            [],
            new Context([])
        );

        $subject = new ChangeCacheTimeout($cacheTagServiceMock);

        $subject->__invoke($event);

        $this->assertEquals(200, $event->getCacheLifetime());
    }
}
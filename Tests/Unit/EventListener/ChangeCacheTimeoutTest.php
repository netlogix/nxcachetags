<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\EventListener;

use Netlogix\Nxcachetags\EventListener\ChangeCacheTimeout;
use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent;
use TYPO3\CMS\Core\Context\Context;

class ChangeCacheTimeoutTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function itChangesCacheTimeoutIfEnvironmentLifetimeIsNotZero(): void
    {
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheTagServiceMock->method('getEnvironmentLifetime')->willReturn(100);

        $event = new ModifyCacheLifetimeForPageEvent(
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
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheTagServiceMock->method('getEnvironmentLifetime')->willReturn(0);

        $event = new ModifyCacheLifetimeForPageEvent(
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
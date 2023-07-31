<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\EventListener;

use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent;

final class ChangeCacheTimeout
{
    public function __construct(
        protected readonly CacheTagService $cacheTagService
    ) {
    }

    public function __invoke(ModifyCacheLifetimeForPageEvent $event): void
    {
        $environmentLifetime = $this->cacheTagService->getEnvironmentLifetime();
        if ($environmentLifetime !== 0) {
            $event->setCacheLifetime(
                min(
                    $event->getCacheLifetime(),
                    $environmentLifetime
                )
            );
        }
    }

}

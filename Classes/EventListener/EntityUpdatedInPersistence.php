<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\EventListener;

use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Extbase\Event\Persistence\EntityUpdatedInPersistenceEvent;

class EntityUpdatedInPersistence
{
    /**
     * @var CacheTagService
     */
    protected CacheTagService $cacheTagService;

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function __invoke(EntityUpdatedInPersistenceEvent $event): void
    {
        $tags = $this->cacheTagService->createCacheTags($event->getObject());

        foreach ($tags as $tag) {
            $this->cacheTagService->flushCachesByTag($tag);
        }
    }
}

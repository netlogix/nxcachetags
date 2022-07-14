<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\EventListener;

use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent;

class EntityAddedToPersistence
{
    /**
     * @var CacheTagService
     */
    protected CacheTagService $cacheTagService;

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function __invoke(EntityAddedToPersistenceEvent $event): void
    {
        $tags = $this->cacheTagService->createCacheTags($event->getObject());

        foreach ($tags as $tag) {
            $this->cacheTagService->flushCachesByTag($tag);
        }
    }
}

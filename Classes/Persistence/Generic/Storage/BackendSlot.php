<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Persistence\Generic\Storage;

use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * This slot for the Typo3DbBackend flushes cache tags whenever Extbase writes database records.
 */
class BackendSlot implements SingletonInterface
{

    /**
     * @var CacheTagService
     */
    protected CacheTagService $cacheTagService;

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function flushCacheForObject(AbstractDomainObject $object)
    {
        $tags = $this->cacheTagService->createCacheTags($object);
        foreach ($tags as $tag) {
            $this->cacheTagService->flushCachesByTag($tag);
        }
    }

}

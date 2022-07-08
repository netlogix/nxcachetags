<?php

namespace Netlogix\Nxcachetags\Persistence\Generic\Storage;

use Netlogix\Nxcachetags\Service\CacheTagService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * This slot for the Typo3DbBackend flushes cache tags whenever Extbase writes database records.
 */
class BackendSlot implements SingletonInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CacheTagService
     */
    protected $cacheTagService;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    protected $newObjectTags = [];

    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    public function flushCacheForObject(AbstractDomainObject $object)
    {
        $tags = $this->cacheTagService->createCacheTags($object);
        foreach ($tags as $tag) {
            $this->cacheTagService->flushCachesByTag($tag);
        }
    }

}

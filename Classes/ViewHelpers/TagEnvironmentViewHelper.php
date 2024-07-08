<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\ViewHelpers;

use Netlogix\Nxcachetags\Service\CacheTagService;
use Netlogix\Nxcachetags\Service\MinimalLifetimeService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TagEnvironmentViewHelper extends AbstractViewHelper
{

    /**
     * @var CacheTagService
     */
    protected CacheTagService $cacheTagService;

    /**
     * @var MinimalLifetimeService
     */
    protected MinimalLifetimeService $minimalLifetimeService;

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function injectMinimalLifetimeService(MinimalLifetimeService $minimalLifetimeService)
    {
        $this->minimalLifetimeService = $minimalLifetimeService;
    }

    public function initializeArguments()
    {
        $this->registerArgument('objectOrCacheTag', 'mixed', '');
        $this->registerArgument('lifetime', 'int', '', false, 0);
        $this->registerArgument('lifetimeSource', 'array', '', false, []);
    }

    public function render()
    {
        $objectOrCacheTag = $this->arguments['objectOrCacheTag'];
        $lifetime = $this->arguments['lifetime'];
        $lifetimeSource = $this->arguments['lifetimeSource'];
        if ($objectOrCacheTag === null || $objectOrCacheTag === '') {
            $objectOrCacheTag = $this->renderChildren();
        }

        $cacheTagsAndIdentifiers = $this->cacheTagService->identifyCacheTagForObject($objectOrCacheTag);

        $lifetime = $this->minimalLifetimeService->findMinimalLifetime(
            $lifetime,
            $this->cacheTagService->identifyCacheTagForObject($objectOrCacheTag),
            $lifetimeSource
        );

        $this->cacheTagService->decreaseEnvironmentLifetime($lifetime);
        $this->cacheTagService->addEnvironmentCacheTags($objectOrCacheTag);
        $this->cacheTagService->addEnvironmentCacheTags($cacheTagsAndIdentifiers);
    }

}

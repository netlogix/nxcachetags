<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheService extends AbstractService implements SingletonInterface
{

    const CACHE_ARGUMENT_CONTENT = 'c';
    const CACHE_ARGUMENT_TAGS = 't';
    const CACHE_ARGUMENT_LIFETIME = 'l';

    /**
     * @var FrontendInterface
     */
    protected FrontendInterface $cache;

    /**
     * @var CacheTagService
     */
    protected CacheTagService $cacheTagService;

    /**
     * @var MinimalLifetimeService
     */
    protected MinimalLifetimeService $minimalLifetimeService;

    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getCache('nxcachetags_cacheviewhelper');
    }

    public function injectCacheTagService(CacheTagService $cacheTagService)
    {
        $this->cacheTagService = $cacheTagService;
    }

    public function injectMinimalLifetimeService(MinimalLifetimeService $minimalLifetimeService)
    {
        $this->minimalLifetimeService = $minimalLifetimeService;
    }

    public function render(
        callable $closure,
        array $identifiedBy,
        int $lifetime = null,
        array $lifetimeSource = [],
        array $taggedBy = [],
        bool $includeLanguage = true,
        bool $includeUserGroups = true,
        bool $includeBackendLogin = true,
        bool $includeRootPage = true
    ): string {

        $identifierParts = [];
        $cacheTagParts = [];
        $identifierSourceParts = [];
        foreach ($identifiedBy as $identifierConfiguration) {

            // The cache identifier is influenced by environment data, like language or fe_user
            $identifierPart = $this->cacheTagService->createCacheIdentifier($identifierConfiguration, $includeLanguage,
                $includeUserGroups, $includeBackendLogin, $includeRootPage);
            if ($identifierPart && !$identifierParts[$identifierPart]) {
                $identifierParts[$identifierPart] = $identifierPart;
            }

            $cacheTags = $this->cacheTagService->createCacheTags($identifierConfiguration);
            foreach ($cacheTags as $cacheTag) {
                if ($cacheTag && !$cacheTagParts[$cacheTag]) {
                    $cacheTagParts[$cacheTag] = $cacheTag;
                }
            }

            $identifierSourcePart = $this->cacheTagService->identifyCacheTagForObject($identifierConfiguration);
            if ($identifierSourcePart) {
                $identifierSourceParts = array_merge($identifierSourceParts, $identifierSourcePart);
            }
        }

        asort($identifierParts);
        $identifier = md5(serialize($identifierParts));

        if (!$this->getTyposcriptFrontendController()->headerNoCache() && $this->cache->has($identifier)) {

            $cacheData = $this->cache->get($identifier);
            foreach ($cacheData[self::CACHE_ARGUMENT_TAGS] as $environmentCacheTags) {
                $this->cacheTagService->addEnvironmentCacheTags($environmentCacheTags);
            }
            $this->cacheTagService->decreaseEnvironmentLifetime($cacheData[self::CACHE_ARGUMENT_LIFETIME]);

        } else {

            $this->cacheTagService->openEnvironment();

            $cacheData = [];
            $cacheData[self::CACHE_ARGUMENT_CONTENT] = call_user_func($closure);
            $this->cacheTagService->addEnvironmentCacheTags($taggedBy);
            $this->cacheTagService->addEnvironmentCacheTags($this->cacheTagService->findTableCacheTagsForLifetimeSources($lifetimeSource,
                $identifierSourceParts));
            $this->cacheTagService->addEnvironmentCacheTags($cacheTagParts);
            $cacheData[self::CACHE_ARGUMENT_TAGS] = $this->cacheTagService->getEnvironmentTags();

            $lifetime = $this->minimalLifetimeService->findMinimalLifetime($lifetime, $identifierSourceParts,
                $lifetimeSource);
            $this->cacheTagService->decreaseEnvironmentLifetime($lifetime);
            $cacheData[self::CACHE_ARGUMENT_LIFETIME] = $this->cacheTagService->getEnvironmentLifetime();

            $this->cache->set($identifier, $cacheData, $cacheData[self::CACHE_ARGUMENT_TAGS],
                $cacheData[self::CACHE_ARGUMENT_LIFETIME]);

            $this->cacheTagService->closeEnvironment();

        }


        return $cacheData[self::CACHE_ARGUMENT_CONTENT];

    }

    protected function getTyposcriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}

<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

use Netlogix\Nxcachetags\ObjectIdentificationHelper\ObjectIdentificationHelperInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Handles cache tags in certain taggable environments.
 */
class CacheTagService extends AbstractService implements SingletonInterface
{

    public const ENVIRONMENT_TAGS = 't';
    public const ENVIRONMENT_LIFETIME = 'l';

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected ConfigurationManagerInterface $configurationManager;

    /**
     * @var CacheManager
     */
    protected CacheManager $cacheManager;

    /**
     * @var ObjectIdentificationHelperInterface[]
     */
    protected array $objectIdentificationHelpers = [];

    /**
     * @var bool[]
     */
    protected array $cacheIdentifierDefaults = [];

    /**
     * An array of environments.
     *
     * The $this->environments[0] is currently active. All others are surrounding
     * environments.
     * Every environment holds both, tags and a corresponding lifetime.
     *
     * @var array
     */
    protected array $environments = [
        [
            self::ENVIRONMENT_TAGS => [],
            self::ENVIRONMENT_LIFETIME => null,
        ],
    ];

    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function initializeObject()
    {
        $this->initializeObjectIdentificationHelpers();
        $this->initializeCacheIdentifierDefaults();
    }

    protected function initializeObjectIdentificationHelpers()
    {
        $settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        foreach ($settings['config.']['tx_nxcachetags.']['settings.']['objectIdentificationHelpers.'] as $key => $objectIdentificationHelperName) {
            $this->objectIdentificationHelpers[$key] = $this->objectManager->get($objectIdentificationHelperName);
        }
        ksort($this->objectIdentificationHelpers);
    }

    protected function initializeCacheIdentifierDefaults()
    {
        $settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        foreach (
            [
                'includeLanguage',
                'includeUserGroups',
                'includeBackendLogin',
                'includeRootPage',
            ] as $argumentName
        ) {
            if (isset($settings['config.']['tx_nxcachetags.']['settings.']['cacheIdentifierDefaults.'][$argumentName])) {
                $this->cacheIdentifierDefaults[$argumentName] = (bool)$settings['config.']['tx_nxcachetags.']['settings.']['cacheIdentifierDefaults.'][$argumentName];
            } else {
                $this->cacheIdentifierDefaults[$argumentName] = true;
            }
        }
    }

    /**
     * Creates a cache tag based on various input types.
     *
     * string, integer, float:
     *     This one is used as cache tag. Add e.g. "pages_5" or "mycachetag".
     *
     * AbstractDomainObject:
     *     The object is converted to the "$tableName_$uid", just like the
     *     TCEmain/DataHandler acts.
     *
     * DataTransferInterface:
     *     Its very innermostSelf is used like normal AbstractDomainObjects.
     *
     * array, Iterator:
     *     All segments are joined by the underscore character, individuals
     *     are treated according to the definition from above.
     *
     * @param mixed $params
     * @param bool $includeLanguage
     * @param bool $includeUserGroups
     * @param bool $includeBackendLogin
     * @param bool $includeRootPage
     * @return string
     * @throws AspectNotFoundException
     */
    public function createCacheIdentifier(
        $params,
        bool $includeLanguage = false,
        bool $includeUserGroups = false,
        bool $includeBackendLogin = false,
        bool $includeRootPage = false
    ): string {
        foreach (
            [
                'includeLanguage',
                'includeUserGroups',
                'includeBackendLogin',
                'includeRootPage',
            ] as $argumentName
        ) {
            if (is_null(${$argumentName})) {
                ${$argumentName} = $this->cacheIdentifierDefaults[$argumentName];
            }
        }

        $controller = $this->getTyposcriptFrontendController();

        $context = GeneralUtility::makeInstance(Context::class);
        $cacheIdentifierModifiers = [
            'includeLanguage' => 'includeLanguage-' . (!$includeLanguage ? '' : implode('-', [
                    $context->getPropertyFromAspect('language', 'id'),
                    $context->getPropertyFromAspect('language', 'contentId'),
                ])),
            'includeUser' => 'includeUser-' . ((!$controller->page['nxcachetags_cacheperuser'] || !$controller->fe_user->user) ? 0 : @intval(
                    $controller->fe_user->user['uid']
                )),
            'includeUserGroups' => 'includeUserGroups-' . (!$includeUserGroups ? '' : implode(
                    '-',
                    $controller->fe_user->groupData['uid']
                )),
            'includeBackendLogin' => 'includeBackendLogin-' . (!$includeBackendLogin ? '' : $context->getPropertyFromAspect(
                    'backend.user',
                    'isLoggedIn',
                    false
                )),
            'params' => $params,
        ];

        return md5(join('_', $this->createCacheTagsInternal($cacheIdentifierModifiers)));
    }

    protected function getTyposcriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Creates cache tags based on various input types.
     *
     * string, integer, float:
     *     This one is used as cache tag. Add e.g. "pages_5" or "mycachetag".
     *
     * AbstractDomainObject:
     *     The object is converted to the "$tableName_$uid", just like the
     *     TCEmain/DataHandler acts.
     *
     * DataTransferInterface:
     *     Its very innermostSelf is used like normal AbstractDomainObjects.
     *
     * array, Iterator:
     *     All segments are joined by the underscore character, individuals
     *     are treated according to the definition from above.
     *
     * @param mixed $params
     * @return array
     */
    protected function createCacheTagsInternal($params): array
    {
        if (is_array($params) && count($params) === 1) {
            $params = reset($params);
        }

        if ($params === null) {
            return [];
        } elseif (is_scalar($params)) {
            return [$params];
        } elseif (is_array($params)) {
            $cacheParts = [];
            foreach ($params as $cachePartSource) {
                $cachePart = $this->createCacheTagsInternal($cachePartSource);
                $cacheParts = array_merge($cacheParts, $cachePart);
            }

            return array_unique($cacheParts);
        } elseif (is_object($params)) {
            return $this->identifyCacheTagForObject($params);
        }

        return [];
    }

    /**
     * @param $params
     * @return array
     */
    public function identifyCacheTagForObject($params): array
    {
        foreach ($this->getObjectIdentificationHelpers() as $objectIdentificationHelper) {
            assert($objectIdentificationHelper instanceof ObjectIdentificationHelperInterface);
            $identifiedContent = $objectIdentificationHelper->identifyCacheTagForObject($params);
            if (!$identifiedContent) {
                continue;
            }
            $combinedIdentifierStrings = $this->createCacheTagsInternal($identifiedContent);
            if (!$combinedIdentifierStrings) {
                continue;
            }

            return $combinedIdentifierStrings;
        }

        return [];
    }

    /**
     * Returns object identification helpers
     *
     * @return ObjectIdentificationHelperInterface[]
     */
    protected function getObjectIdentificationHelpers(): array
    {
        return $this->objectIdentificationHelpers;
    }

    public function exposeCacheLifetime(array $params, TypoScriptFrontendController $tsfe)
    {
        $environmentLifetime = $this->getEnvironmentLifetime();
        if (!$environmentLifetime) {
            return $params['cacheTimeout'];
        } else {
            return min($params['cacheTimeout'], $environmentLifetime);
        }
    }

    /**
     * Returns the lifetime set for this environment.
     */
    public function getEnvironmentLifetime(): int
    {
        return $this->environments[0][self::ENVIRONMENT_LIFETIME];
    }

    /**
     * Adds new cache tags to all open environments, which also includes
     * the page cache. This does not cover sibling environments and even
     * not child environment. If a child environment needs to be tagged
     * as well, this method must be added there, too.
     *
     * @param mixed $objectOrCacheTag
     * @return void
     */
    public function addEnvironmentCacheTags($objectOrCacheTag)
    {
        $tagNames = $this->createCacheTags($objectOrCacheTag);
        $this->addPageCacheTags($tagNames);
        foreach ($this->environments as &$environment) {
            foreach ($tagNames as $tagName) {
                $environment[self::ENVIRONMENT_TAGS][$tagName] = $tagName;
            }
        }
    }

    /**
     * Creates cache tags based on various input types.
     *
     * string, integer, float:
     *     This one is used as cache tag. Add e.g. "pages_5" or "mycachetag".
     *
     * AbstractDomainObject:
     *     The object is converted to the "$tableName_$uid", just like the
     *     TCEmain/DataHandler acts.
     *
     * DataTransferInterface:
     *     Its very innermostSelf is used like normal AbstractDomainObjects.
     *
     * array, Iterator:
     *     All segments are joined by the underscore character, individuals
     *     are treated according to the definition from above.
     *
     * @param mixed $params
     * @return array
     */
    public function createCacheTags($params): array
    {
        $cacheTags = $this->createCacheTagsInternal($params);
        foreach ($cacheTags as &$cacheTag) {
            $cacheTag = preg_replace('/[^a-zA-Z\d_-]+/', '_', $cacheTag);
            if (strlen($cacheTag) > 250) {
                $cacheTag = substr($cacheTag, 0, 100) . md5($cacheTag) . substr($cacheTag, -100);
            }
        }

        return $cacheTags;
    }

    /**
     * Adds new cache tags to the page cache.
     */
    public function addPageCacheTags($objectOrCacheTag)
    {
        $tagNames = $this->createCacheTags($objectOrCacheTag);
        $this->getTyposcriptFrontendController()->addCacheTags($tagNames);
    }

    public function decreaseEnvironmentLifetime(int $lifetime)
    {
        foreach ($this->environments as &$environment) {
            if ($environment[self::ENVIRONMENT_LIFETIME] === 0 || ($environment[self::ENVIRONMENT_LIFETIME] > $lifetime)) {
                $environment[self::ENVIRONMENT_LIFETIME] = $lifetime;
            }
        }
    }

    /**
     * Flushes entries tagged by the specified tag of all registered
     * caches.
     */
    public function flushCachesByTag($objectOrCacheTag)
    {
        foreach ($this->createCacheTags($objectOrCacheTag) as $tag) {
            $this->cacheManager->flushCachesByTag($tag);
        }
    }

    /**
     * Creates a new cache tag environment
     */
    public function openEnvironment()
    {
        array_unshift($this->environments, [
            self::ENVIRONMENT_LIFETIME => 0,
            self::ENVIRONMENT_TAGS => [],
        ]);
    }

    /**
     * Closes the current cache tag environment.
     */
    public function closeEnvironment()
    {
        array_shift($this->environments);
    }

    /**
     * Returns all cache tags being applied to this environment.
     */
    public function getEnvironmentTags()
    {
        return $this->environments[0][self::ENVIRONMENT_TAGS];
    }

    /**
     * @param array $lifetimeSource
     * @param array $identifiers
     * @return array
     */
    public function findTableCacheTagsForLifetimeSources(array $lifetimeSource = [], array $identifiers = []): array
    {
        $lifetimeSource = $this->filterValidLifetimeSourceTables($lifetimeSource);

        foreach ($identifiers as $identifier) {
            if (isset($lifetimeSource[$identifier])) {
                unset($lifetimeSource[$identifier]);
            } elseif (preg_match('%^(?<table>[a-z\d_-]+)[_:](?<uid>\d+)$%', $identifier, $matches)) {
                if (isset($lifetimeSource[$matches['table']])) {
                    unset($lifetimeSource[$matches['table']]);
                }
            }
        }

        return $lifetimeSource;
    }

}

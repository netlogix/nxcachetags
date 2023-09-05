<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase'][] = \Netlogix\Nxcachetags\Service\UserToHashBaseService::class . '->createHashBase';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \Netlogix\Nxcachetags\Hooks\DataHandler::class . '->clearCachePostProc';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['get_cache_timeout'][] = \Netlogix\Nxcachetags\Service\CacheTagService::class . '->exposeCacheLifetime';

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nxcachetags_cacheviewhelper'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nxcachetags_cacheviewhelper'] = [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
            'options' => [],
            'groups' => ['pages', 'all']
        ];
    }

});

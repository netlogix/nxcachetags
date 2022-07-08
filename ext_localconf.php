<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase'][] = \Netlogix\Nxcachetags\Service\UserToHashBaseService::class . '->createHashBase';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \Netlogix\Nxcachetags\Hooks\DataHandler::class . '->clearCachePostProc';

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nxcachetags_cacheviewhelper'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nxcachetags_cacheviewhelper'] = [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
            'options' => [],
            'groups' => ['pages', 'all']
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pages'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \Netlogix\Nxcachetags\Cache\Backend\RetrievableTagsProxyBackend::class,
        'options' => [],
        'groups' => ['pages', 'all']
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pages_nxcachetags_proxy'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \Netlogix\Nxcachetags\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => [],
        'groups' => ['pages', 'all']
    ];

    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    foreach (['afterInsertObject', 'afterUpdateObject', 'flushCacheForObject'] as $command) {
        $dispatcher->connect(
            \TYPO3\CMS\Extbase\Persistence\Generic\Backend::class,
            $command,
            \Netlogix\Nxcachetags\Persistence\Generic\Storage\BackendSlot::class,
            'flushCacheForObject'
        );
    }

});

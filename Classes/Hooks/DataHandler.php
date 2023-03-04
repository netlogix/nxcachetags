<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandler implements SingletonInterface
{

    /**
     * @var array
     */
    protected array $tagsToFlush = [];

    public function processCmdmap_preProcess(
        string $command,
        string $table,
        int $id,
        $value,
        \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
    ) {
        if (!$this->isRelevantRequestType()) {
            return;
        }

        if ($table === 'pages' && $command === 'move') {
            $this->addRootlineTags($id);
        }
    }

    /**
     * Check if the current request type is relevant for this hook.
     *
     * This is a wrapper for better testability
     *
     * @return bool
     */
    protected function isRelevantRequestType(): bool {
        return !Environment::isCli();
    }

    protected function addRootlineTags(int $pageUid)
    {
        $pageRecord = BackendUtility::getRecord('pages', $pageUid);
        $cacheBuster = is_array($pageRecord) ? $pageRecord['pid'] : $pageUid;
        // Hack to avoid static cache after move
        foreach (BackendUtility::BEgetRootLine($pageUid, 'AND ' . $cacheBuster . '=' . $cacheBuster) as $page) {
            $this->tagsToFlush[] = 'rootline_' . $page['uid'];
        }
    }

    /**
     * Flushes the cache if a news record was edited.
     */
    public function clearCachePostProc(array $params, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        if (!$this->isRelevantRequestType()) {
            return;
        }

        if (!array_key_exists('table', $params)) {
            return;
        }

        if (isset($dataHandler->datamap[$params['table']][$params['uid']]['l10n_parent'])) {
            $this->tagsToFlush[] = $params['table'] . '_' . $dataHandler->datamap[$params['table']][$params['uid']]['l10n_parent'];
        }

        if ($params['table'] === 'pages') {
            $pageUid = $params['uid'];
            $this->addRootlineTags($pageUid);
        }

        if ($this->tagsToFlush) {
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            foreach (array_unique($this->tagsToFlush) as $tag) {
                $cacheManager->flushCachesInGroupByTag('pages', $tag);
            }
            $this->tagsToFlush = [];
        }
    }

}

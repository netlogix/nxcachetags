<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\EventListener;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\Event\AfterFileContentsSetEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileDeletedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileMovedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileRenamedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FlushCacheTagForFile
{
    public function __construct(
        protected readonly CacheManager $cacheManager
    ) {
    }

    public function afterFileMoved(AfterFileMovedEvent $event): void
    {
        $this->flushCacheByFile($event->getFile());
    }

    public function afterFileDeleted(AfterFileDeletedEvent $event): void
    {
        $this->flushCacheByFile($event->getFile());
    }

    public function afterFileRenamed(AfterFileRenamedEvent $event): void
    {
        $this->flushCacheByFile($event->getFile());
    }

    public function afterFileReplaced(AfterFileReplacedEvent $event): void
    {
        $this->flushCacheByFile($event->getFile());
    }

    public function afterFileContentsSet(AfterFileContentsSetEvent $event): void
    {
        $this->flushCacheByFile($event->getFile());
    }

    private function flushCacheByFile(FileInterface $file)
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesByTag('sys_file_' . (int) $file->getProperty('uid'));
    }

}

<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\EventListener;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class GeneratePublicUrlForResource
{

    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        if ($this->getTypoScriptFrontendController() === null) {
            return;
        }

        $resource = $event->getResource();
        if (!$resource instanceof FileInterface) {
            return;
        }

        if ($resource instanceof ProcessedFile) {
            $resource = $resource->getOriginalFile();
        }

        // Do not add cache tags more then once
        $pageCacheTags = array_flip($this->getTypoScriptFrontendController()->getPageCacheTags());
        $fileCacheTag = sprintf('sys_file_%s', $resource->getUid());
        if (array_key_exists($fileCacheTag, $pageCacheTags)) {
            return;
        }

        $cacheTags = [$fileCacheTag];

        $metaData = $resource->getMetaData()?->get() ?? [];
        if (array_key_exists('uid', $metaData)) {
            $cacheTags[] = sprintf('sys_file_metadata_%s', $metaData['uid']);
        }

        $this->getTypoScriptFrontendController()->addCacheTags($cacheTags);
    }

    protected function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}

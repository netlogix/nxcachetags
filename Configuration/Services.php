<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags;

use Netlogix\Nxcachetags\EventListener\ChangeCacheTimeout;
use Netlogix\Nxcachetags\EventListener\EntityAddedToPersistence;
use Netlogix\Nxcachetags\EventListener\EntityRemovedFromPersistence;
use Netlogix\Nxcachetags\EventListener\EntityUpdatedInPersistence;
use Netlogix\Nxcachetags\EventListener\FlushCacheTagForFile;
use Netlogix\Nxcachetags\EventListener\GeneratePublicUrlForResource;
use Netlogix\Nxcachetags\Service\RenderingContextIdentificationService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

return function (ContainerConfigurator $containerConfigurator){
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Netlogix\\Nxcachetags\\', '../Classes/');

    $services->set(RenderingContextIdentificationService::class)->autowire(
        false
    );

    $services->set(EntityAddedToPersistence::class)->tag(
        'event.listener',
        [
            'name' => 'event.listener',
            'identifier' => 'netlogix/nxcachetags/afterInsertObject',
            'event' => 'TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent'
        ]
    );
    $services->set(EntityUpdatedInPersistence::class)->tag(
        'event.listener',
        [
            'name' => 'event.listener',
            'identifier' => 'netlogix/nxcachetags/afterUpdateObject',
            'event' => 'TYPO3\CMS\Extbase\Event\Persistence\EntityUpdatedInPersistenceEvent'
        ]
    );
    $services->set(EntityRemovedFromPersistence::class)->tag(
        'event.listener',
        [
            'name' => 'event.listener',
            'identifier' => 'netlogix/nxcachetags/afterRemoveObject',
            'event' => 'TYPO3\CMS\Extbase\Event\Persistence\EntityRemovedFromPersistenceEvent'
        ]
    );
        $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
        if ($versionInformation->getMajorVersion() < 12) {
        $services->set(ChangeCacheTimeout::class)->tag(
            'event.listener',
            [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/cache-timeout',
                'event' => 'TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent'
            ]
        );
    }
    $services->set(GeneratePublicUrlForResource::class)->tag(
        'event.listener',
        [
            'name' => 'event.listener',
            'identifier' => 'netlogix/nxcachetags/generatepublicurlforresource',
            'event' => 'TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent'
        ]
    );
    $services->set(FlushCacheTagForFile::class)
        ->tag(
            'event.listener',
            [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/flushCacheAfterFileContentsSet',
                'method' => 'afterFileContentsSet',
                'event' => 'TYPO3\CMS\Core\Resource\Event\AfterFileContentsSetEvent'
            ])
        ->tag(
            'event.listener',
            [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/flushCacheAfterFileDeleted',
                'method' => 'afterFileDeleted',
                'event' => 'TYPO3\CMS\Core\Resource\Event\AfterFileDeletedEvent'
            ])
        ->tag(
            'event.listener',
            [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/flushCacheAfterFileMoved',
                'method' => 'afterFileMoved',
                'event' => 'TYPO3\CMS\Core\Resource\Event\AfterFileMovedEvent'
            ])
        ->tag(
            'event.listener',
                [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/flushCacheAfterFileRenamed',
                'method' => 'afterFileRenamed',
                'event' => 'TYPO3\CMS\Core\Resource\Event\AfterFileRenamedEvent'
            ])
            ->tag(
            'event.listener',
            [
                'name' => 'event.listener',
                'identifier' => 'netlogix/nxcachetags/fluhCacheAfterFileReplaced',
                'method' => 'afterFileReplaced',
                'event' => 'TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent'
            ]);
};
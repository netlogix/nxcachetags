<?php

namespace Netlogix\Nxcachetags\ObjectIdentificationHelper;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;

/**
 * Identify a given object if possible.
 */
class FalObjectIdentificationHelper implements ObjectIdentificationHelperInterface
{

    /**
     * Returns cache cache tag parts for the given object if known.
     *
     * @param mixed $object
     * @return array
     */
    public function identifyCacheTagForObject($object): array
    {
        $tagData = [];
        if ($object instanceof FileReference) {
            $tagData[] = $this->getReferenceTag($object);
            $tagData[] = $object->getOriginalFile();
        } elseif ($object instanceof File) {
            $tagData[] = $this->getFileTag($object);
            $tagData[] = $object->getStorage();
            $tagData[] = $this->getFileMetadataTag($object);
        } elseif ($object instanceof ResourceStorage) {
            $tagData[] = $this->getStorageTag($object);
        } elseif ($object instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
            $tagData[] = $object->getOriginalResource();
        } elseif ($object instanceof \TYPO3\CMS\Extbase\Domain\Model\File) {
            $tagData[] = $object->getOriginalResource();
        }

        return $tagData;
    }

    protected function getReferenceTag(FileReference $fileReference): string
    {
        return 'sys_file_reference_' . $fileReference->getUid();
    }

    protected function getFileTag(File $file): string
    {
        return 'sys_file_' . $file->getUid();
    }

    protected function getFileMetadataTag(File $file): string
    {
        return 'sys_file_metadata_' . $file->_getMetaData()['uid'];
    }

    protected function getStorageTag(ResourceStorage $storage): string
    {
        return 'sys_file_storage_' . $storage->getUid();
    }

}

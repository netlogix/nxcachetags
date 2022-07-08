<?php

namespace Netlogix\Nxcachetags\ObjectIdentificationHelper;

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Identify a given object if possible.
 */
class DomainObjectIdentificationHelper implements ObjectIdentificationHelperInterface
{

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * Returns cache cache tag parts for the given object if known.
     *
     * @param mixed $object
     * @return array
     */
    public function identifyCacheTagForObject($object): array
    {
        $cacheTags = [];
        if ($object instanceof AbstractDomainObject) {
            $tableName = $this->dataMapper->convertClassNameToTableName(get_class($object));
            $recordUid = $object->getUid();

            $cacheTags[] = $tableName . '_' . $recordUid;
        } elseif ($object instanceof QueryResultInterface) {
            $cacheTags[] = $this->dataMapper->convertClassNameToTableName($object->getQuery()->getType());
        }

        return $cacheTags;
    }

}

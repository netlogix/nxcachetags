<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\ObjectIdentificationHelper;

use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Allow for several variants of cache tags which all target a specific
 * row in the database.
 *
 * Possible formats:
 * - pages_100
 * - pages-100
 * - pages:100
 */
class TcaRecordIdentificationHelper implements ObjectIdentificationHelperInterface
{
    /**
     * @var DataMapper
     */
    protected DataMapper $dataMapper;

    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * @param $object
     * @return string[]
     */
    public function identifyCacheTagForObject($object): array
    {
        if (!is_string($object)) {
            return [];
        }
        if (!preg_match('%^(?<tableName>[a-z\d_]+)\s*[_:-]\s*(?<recordUid>\\d+)$%', $object, $matches)) {
            return [];
        }
        return [
            $matches['tableName'] . '_' . $matches['recordUid']
        ];
    }
}
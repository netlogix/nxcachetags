<?php

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
    protected $dataMapper;

    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * @param $cacheTag
     * @return string[]
     */
    public function identifyCacheTagForObject($cacheTag): array
    {
        if (!is_string($cacheTag)) {
            return [];
        }
        if (!preg_match('%^(?<tableName>[a-z0-9_]+)\s*[_:-]\s*(?<recordUid>\\d+)$%', $cacheTag, $matches)) {
            return [];
        }
        return [
            $matches['tableName'] . '_' . $matches['recordUid']
        ];
    }
}
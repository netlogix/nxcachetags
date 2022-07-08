<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

/**
 * This service is meant to hold several generic functions handling
 * objects, tags and identifiers that are used in both, the
 * cacheTagService as well as the minimalLifetimeService.
 */
abstract class AbstractService
{

    protected function filterValidLifetimeSourceTables(array $lifetimeSource = []): array
    {
        $validLifetimeSource = [];
        foreach ($lifetimeSource as $tableName) {
            if (!isset($GLOBALS['TCA'][$tableName])) {
                continue;
            }
            if (isset($validLifetimeSource[$tableName])) {
                continue;
            }
            $validLifetimeSource[$tableName] = $tableName;
        }

        return $validLifetimeSource;
    }

}

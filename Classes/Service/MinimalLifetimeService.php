<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MinimalLifetimeService extends AbstractService implements SingletonInterface
{

    /**
     * @var ConfigurationManagerInterface
     */
    protected ConfigurationManagerInterface $configurationManager;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function findMinimalLifetime(int $lifetime, array $identifiers = [], array $lifetimeSource = []): int
    {
        $now = $GLOBALS['ACCESS_TIME'];
        if (!$lifetime) {
            $expires = PHP_INT_MAX;
        } else {
            $expires = $now + $lifetime;
        }

        $lifetimeSource = $this->filterValidLifetimeSourceTables($lifetimeSource);

        foreach ($identifiers as $identifier) {
            if (preg_match('%^(?<table>[a-z\d_-]+)[_:](?<uid>\d+)$%', $identifier, $matches)) {
                $expires = $this->findMinimalLifetimeForRecord($expires, $matches['table'], (int)$matches['uid']);
                if (isset($lifetimeSource[$matches['table']])) {
                    unset($lifetimeSource[$matches['table']]);
                }
            }
        }

        $storagePids = $this->getStoragePids($lifetimeSource);

        foreach ($lifetimeSource as $lifetimeSourceTable) {
            $expires = $this->findMinimalLifetimeForTable($expires, $lifetimeSourceTable, $storagePids);
        }

        if ($expires === PHP_INT_MAX) {
            return 0;
        } else {
            return (int)($expires - $now);
        }
    }

    protected function findMinimalLifetimeForRecord(int $expires, string $tableName, int $uid): int
    {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            return $expires;
        }
        $now = $GLOBALS['ACCESS_TIME'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);

        $record = $queryBuilder
            ->select('*')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAssociative();
        if (!$record) {
            return $expires;
        }

        foreach (['starttime', 'endtime'] as $field) {
            // Note: there is no need to load TCA because we need only enable columns!
            if (isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'][$field])) {
                $candidate = (int)$record[$GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'][$field]];
                if ($candidate > $now) {
                    $expires = min($expires, $candidate);
                }
            }
        }

        return $expires;
    }

    protected function getStoragePids(array $lifetimeSource = []): array
    {
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        foreach ($lifetimeSource as $tableName) {
            if (in_array($tableName, $frameworkConfiguration['persistence']['noStoragePidForCacheLifetime'] ?? [])) {
                return [];
            }
        }
        $storagePids = array_unique(
            GeneralUtility::intExplode(',', $frameworkConfiguration['persistence']['storagePid'] ?? '')
        );

        $storagePids = array_flip($storagePids);
        if (isset($storagePids[0])) {
            unset($storagePids[0]);
        }
        $storagePids = array_flip($storagePids);

        return array_values($storagePids);
    }

    protected function findMinimalLifetimeForTable(int $expires, string $tableName, array $storagePids = []): int
    {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            return $expires;
        }

        if (in_array($tableName, ['tt_content', 'sys_file_reference'])) {
            $storagePids[] = (int)$this->getTyposcriptFrontendController()->id;
        }

        foreach (['starttime', 'endtime'] as $field) {
            if (isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'][$field])) {
                $enableField = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'][$field];
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
                    $tableName
                );

                $select = sprintf('MIN(%s) AS minValue', $enableField);
                $query = $queryBuilder
                    ->selectLiteral($select)
                    ->from($tableName)
                    ->where(
                        $queryBuilder->expr()->gt(
                            $enableField,
                            $queryBuilder->createNamedParameter($GLOBALS['ACCESS_TIME'], PDO::PARAM_INT)
                        )
                    )
                    ->setMaxResults(1);

                if ($storagePids) {
                    $query->andWhere(
                        $queryBuilder->expr()->in('pid', array_map('intval', $storagePids))
                    );
                }
                $row = $query->execute()->fetchAssociative();
                if ($row && !is_null($row['minValue'])) {
                    $expires = (int)min($expires, $row['minValue']);
                }
            }
        }

        return $expires;
    }

    protected function getTyposcriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}

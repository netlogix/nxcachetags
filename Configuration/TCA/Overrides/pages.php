<?php
declare(strict_types=1);
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    $tempColumns = array(
        'nxcachetags_cacheperuser' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:nxcachetags/Resources/Private/Language/locallang.xlf:pages.nxcachetags_cacheperuser',
            'config' => array(
                'type' => 'check',
                'items' => array(
                    '1' => array(
                        '0' => 'LLL:EXT:nxcachetags/Resources/Private/Language/locallang.xlf:pages.nxcachetags_cacheperuser.checkbox_1_formlabel',
                    )
                )
            )
        ),
    );

    ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);
    ExtensionManagementUtility::addFieldsToPalette('pages', 'caching', 'nxcachetags_cacheperuser');
});

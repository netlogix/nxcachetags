<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    $tempColumns = [
        'nxcachetags_cacheperuser' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:nxcachetags/Resources/Private/Language/locallang.xlf:pages.nxcachetags_cacheperuser',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:nxcachetags/Resources/Private/Language/locallang.xlf:pages.nxcachetags_cacheperuser.checkbox_1_formlabel',
                    ]
                ]
            ]
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);
    ExtensionManagementUtility::addFieldsToPalette('pages', 'caching', 'nxcachetags_cacheperuser');
});

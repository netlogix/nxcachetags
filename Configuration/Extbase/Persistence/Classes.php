<?php

declare(strict_types=1);

use Netlogix\Nxcachetags\Domain\Model\BackendUser;
use Netlogix\Nxcachetags\Domain\Model\BackendUserGroup;
use Netlogix\Nxcachetags\Domain\Model\Category;
use Netlogix\Nxcachetags\Domain\Model\FrontendUser;
use Netlogix\Nxcachetags\Domain\Model\FrontendUserGroup;

return [
    BackendUser::class => [
        'tableName' => 'be_users',
    ],
    BackendUserGroup::class => [
        'tableName' => 'be_groups',
    ],
    Category::class => [
        'tableName' => 'sys_category',
    ],
    FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ]
];
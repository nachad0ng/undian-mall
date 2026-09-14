<?php

return [
    [
        'label' => 'Dashboard',
        'icon' => 'bi-house',
        'route' => 'dashboard',
        'active' => 'dashboard',
        'permission' => null,
    ],

    [
        'label' => 'Master Data',
        'icon' => 'bi-tags',
        'permission' => ['manage-periods', 'manage-tenants', 'manage-customers', 'manage-prizes', 'manage-payment-types'],
        'children' => [
            [
                'label' => 'Periode Undian',
                'route' => 'admin.raffle-periods.index',
                'active' => 'admin.raffle-periods.*',
                'permission' => 'manage-periods',
            ],
            [
                'label' => 'Tenant',
                'route' => 'admin.tenants.index',
                'active' => 'admin.tenants.*',
                'permission' => 'manage-tenants',
            ],
            [
                'label' => 'Pelanggan',
                'route' => 'admin.customers.index',
                'active' => 'admin.customers.*',
                'permission' => 'manage-customers',
            ],
            [
                'label' => 'Hadiah',
                'route' => 'admin.prizes.index',
                'active' => 'admin.prizes.*',
                'permission' => 'manage-prizes',
            ],
            [
                'label' => 'Tipe Pembayaran',
                'route' => 'admin.payment-types.index',
                'active' => 'admin.payment-types.*',
                'permission' => 'manage-payment-types',
            ],
            [
                'label' => 'Bonus Poin Pembayaran',
                'route' => 'admin.bonus-point-rules.index',
                'active' => 'admin.bonus-point-rules.*',
                'permission' => 'manage-prizes',
            ],
        ],
    ],

    [
        'label' => 'Transaction',
        'icon' => 'bi-arrow-left-right',
        'permission' => ['manage-transactions', 'view-point-exchange-history'],
        'children' => [
            [
                'label' => 'Tukar Struk & Riwayat',
                'route' => 'admin.point-exchange.history',
                'active' => 'admin.point-exchange.*',
                'permission' => ['manage-transactions', 'view-point-exchange-history'],
            ],
        ],
    ],

    [
        'label' => 'Laporan',
        'icon' => 'bi-file-earmark-text',
        'permission' => ['view-reports', 'view-audit-logs'],
        'children' => [
            [
                'label' => 'Poin Redemption',
                'route' => 'admin.reports.point-redemptions.index',
                'active' => 'admin.reports.point-redemptions.*',
                'permission' => ['view-reports', 'manage-prizes', 'manage-users'],
            ],
            // [
            //     'label' => 'Saldo Poin Customer',
            //     'route' => 'admin.reports.customer-point-balances.index',
            //     'active' => 'admin.reports.customer-point-balances.*',
            //     'permission' => ['view-reports', 'manage-prizes', 'manage-users'],
            // ],
            [
                'label' => 'Pemenang Undian',
                'route' => 'admin.reports.winners.index',
                'active' => 'admin.reports.winners.*',
                'permission' => ['view-reports', 'manage-prizes', 'manage-users'],
            ],
            [
                'label' => 'Audit Log',
                'route' => 'admin.reports.audit-logs.index',
                'active' => 'admin.reports.audit-logs.*',
                'permission' => 'view-audit-logs',
            ],
        ],
    ],

    [
        'label' => 'Admin',
        'icon' => 'bi-gear-wide-connected',
        'permission' => ['manage-users', 'manage-roles', 'manage-permissions'],
        'children' => [
            [
                'label' => 'Users',
                'route' => 'admin.users.index',
                'active' => 'admin.users.*',
                'permission' => 'manage-users',
            ],
            [
                'label' => 'Roles',
                'route' => 'admin.roles.index',
                'active' => 'admin.roles.*',
                'permission' => 'manage-roles',
            ],
            [
                'label' => 'Permissions',
                'route' => 'admin.permissions.index',
                'active' => 'admin.permissions.*',
                'permission' => 'manage-permissions',
            ],
        ],
    ],
];

<?php

return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_morph_type' => 'model_type',
    ],

    'cache' => [
        'expiration_time' => 60 * 60 * 24,
    ],
];

<?php

return [
    /**
     * DB Column name from model
     */
    'column_name' => env('STATIC_ROLE_COLUMN_NAME', 'role'),

    /**
     * Roles with permission as path
     *
     * - `+` Wildcard one level
     * - `#` Wildcard everything following
     * - `!` Before the permission - prohibits permission
     *
     * 'admin' => [
     *     'users/#',
     *     'users/+/field',
     *     '!users/create'
     * ]
     */
    'roles' => [],

];

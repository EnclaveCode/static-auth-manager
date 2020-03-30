<?php

return [
    /**
     * DB Column name from model
     */
    'column_name' => env('SAM_ROLE_COLUMN_NAME', 'role'),

    /**
     * Roles with permission as path
     *
     * - `*` Wildcard everything following
     *
     * 'admin' => [
     *      'users/*',
     * ],
     * 'user' => [
     *     'users/create'
     * ]
     *
     */
    'roles' => [],

];

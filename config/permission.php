<?php

return [
    /**
     * DB Column name from model
     */
    'column_name' => env('SAM_ROLE_COLUMN_NAME', 'role'),

    /**
     * Roles with permission as path
     *
     * - `+` Wildcard one level //@TODO - DO USUNIĘCIA
     * - `#` Wildcard everything following //@TODO - ZMIANA NA GWIAZDKĘ
     * - `!` Before the permission - prohibits permission //@TODO - DO USUNIĘCIA
     *
     * 'admin' => [
     *     'users/#',
     *     'users/+/field',
     *     '!users/create'
     * ]
     * ---------------
     * 'admin' => [
     *      'users/#',
     * ],
     * 'user' => [
     *     'users/create'
     * ]
     *
     */
    'roles' => [],

];

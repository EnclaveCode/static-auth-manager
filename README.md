# Laravel StaticAuthManager

Manage user permissions and roles in your Laravel application by domain driven rules.

* [Installation](#installation)
* [Usage](#usage)
  * [Using roles](#using-roles)
  * [Using permissions](#using-permissions)
  * [Using Blade directives](#using-blade-directives)
  * [Using middleware](#Middleware)
* [Config](#config)

## Example

### Add single role
```php
$user->assignRole('admin');

$user->hasRole('admin'); // true
```

### Add many roles
```php
$user->assignRole(['admin','user']);

$user->hasRole('admin'); // true
$user->hasRole('user'); // true

```

You can define roles and permissions by code at `config/permission.php`.

```php
'role' => [
  'admin' => [
    'news/*', // Allow all paths beginning with news/
  ],
  'editor' => [
    'news/*',
  ],
  'user' => [
    'news/show', // Explicitly allow news/show
  ],
]
```

You can check permissions by

```php
$admin->hasPermissionTo('news/delete'); // true
$editor->hasPermissionTo('news/delete'); // false
$user->hasPermissionTo('news/delete'); // false
```

## Installation

//@TODO Change instalation

```bash
composer require enclave/laravel-static-permission
```

Older than Laravel 5.5 need a service provider registration.

```php
// config/app.php

'providers' => [
  Enclave\StaticAuthManager\Providers\PermissionServiceProvider::class,
];
```

```php
php artisan vendor:publish
```

## Usage

### Add trait to model

```php
  use HasRoles;
```

### Using roles

You can define the roles in the `config/permission.php` file.

```php
// config/permission.php

'roles' => [
  'role_name' => [],
  'admin' => [],
],
```
#### Assign role/roles

Add a role to a model.

```php
$model->assignRole('admin');
```

Add a roles to a model.

```php
$model->assignRole(['admin','user']);
```

#### Check role/roles

You can check the roles via:

```php
$model->hasRole('admin');

$model->getRoles(); // return collection(['admin'])

```

```php
$model->hasRole(['admin','user']);


$model->getRoles(); // return collection(['admin','user']);

```

#### Detach role/roles

You can detach the roles via:

```php
$model->assignRole(['admin','user']);
$model->detachRole('admin');


$model->getRoles(); // return collection(['user'])
```

### Using permissions

Permissions are based on the MQTT syntax. Permissions are specified as path. Thus, individual security levels can be mapped and generally released via wildcards.

#### Check permissions

```php
$model->hasPermissionTo('users/show/email');
```

```php
$model->hasPermissionTo(['users/show', 'users/edit']);
```

```php
$model->hasAnyPermission('users/show/email');
```

```php
$model->hasAnyPermission(['users/show', 'users/edit']);
```

#### Configuration

- `*` Wildcard for everything following

You can define the role permissions in the `config/permission.php` file.

```php
// config/permission.php

'roles' => [
  'role_name' => [
    'users/*'
  ],
  'admin' => [
    'users/create',
  ],
],
```

### Using Blade directives

You can use Blade directives in your views.

#### Role

```blade
@role('admin')
  Show if user is admin
@endrole
```

```blade
@unlessrole('admin')
  Show if user is not admin
@endunlessrole
```

#### Permission

```blade
@permission('user/edit')
  Show if user has rights to user/edit
@endpermission
```

You can use several permissions too.

```blade
@permission('user/edit|user/create')
  Show if user has rights to user/edit AND user/create
@endpermission
```

```blade
@anypermission('user/edit|user/create')
 Show if user has rights to user/edit OR user/create
@endanypermission
```

### Middleware
Add the middleware to your `src/Http/Kernel.php`
```php
use Enclave\StaticAuthManager\Middlewares\HasRoleMiddleware;
use Enclave\StaticAuthManager\Middlewares\HasAnyPermissionMiddleware;


class Kernel extends HttpKernel
{
... 
  protected $routeMiddleware = [
    ...
    'permission' => HasAnyPermissionMiddleware::class
    'role' => HasRoleMiddleware::class

  ]

}
```

And use it like 
```php
// If user has 'admin' or 'user' role
Route::group(['middleware' => ['role:admin|user']], function () {
    //
})

// If user has 'admin' role
Route::group(['middleware' => ['role:admin']], function () {
    //
})

// If user has 'user/create'
Route::group(['middleware' => ['permission:create/user']], function () {
    //
})

// If user has 'user/create' or 'user/edit'
Route::group(['middleware' => ['permission:create/user|user/edit']], function () {
    //
})
```

## Config

Example Config

```php
<?php
// config/permission.php

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

```

Additional config in .env
```bash
# StaticAuthManager - column name in user model
SAM_ROLE_COLUMN_NAME='role' 
```

## Testing

```bash
composer test
# same to
./vendor/bin/phpunit

```

## Credits

Primarily forked from [sourceboat/laravel-static-permission](https://github.com/sourceboat/laravel-static-permission).


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

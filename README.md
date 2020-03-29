# laravel-static-permission


Manage user permissions and roles in your Laravel application by domain driven rules.

* [Installation](#installation)
* [Usage](#usage)
  * [Usign roles](#using-roles)
  * [Usign permissions](#using-permissions)
  * [Using Blade directives](#using-blade-directives)
* [Config](#config)

## Example

```php
$user->assignRole('admin');

$user->hasRole('admin'); // true
```

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
  Enclave\StaticAuthManager\PermissionServiceProvider::class,
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

#### Check role

You can check the roles via:

```php
$model->hasRole('admin');
$model->hasRole(['admin','user']);


$model->getRoles(); // return admin
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

#### Middleware
Add the middleware to your `src/Http/Kernel.php`
```php
use Enclave\StaticAuthManager\Middlewares\RoleMiddleware;
class Kernel extends HttpKernel
{
... 
  protected $routeMiddleware = [
    ...
    'role' => RoleMiddleware::class
  ]

}
```

And use it like 
```php
Route::group(['middleware' => ['role:admin']], function () {
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
     * Column name of the model
     */
    'column_name' => 'role',

    /**
     * Roles with permissions
     *
     * - `*` Wildcard everything following
     *
     * 'admin' => [
     *     'users/*',
     * ]
     */
    'roles' => [],

];

```

## Testing

```bash
composer test
```

## Contributing

```bash
composer lint:phpcs
composer lint:phpmd
```

## Credits

This package is heavily inspired by [Spatie / laravel-permission](https://github.com/spatie/laravel-permission).

- [Philipp Kübler](https://github.com/pkuebler)
- [All Contributors](https://github.com/enclave/laravel-static-permission/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

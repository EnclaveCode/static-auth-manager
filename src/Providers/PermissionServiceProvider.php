<?php

namespace EnclaveCode\StaticAuthManager\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class PermissionServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../../config/permission.php' => config_path('permission.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/permission.php', 'permission');

        $this->registerBladeExtensions();
    }

    protected function registerBladeExtensions(): void
    {

        $bladeExtensions = static function (BladeCompiler $bladeCompiler): void {
            self::registerRoleBladeExtensions($bladeCompiler);
            self::registerUnlessRoleBladeExtensions($bladeCompiler);
            self::registerPermissionBladeExtensions($bladeCompiler);
            self::registerAnyPermissionBladeExtensions($bladeCompiler);
        };

        $this->app->afterResolving('blade.compiler', $bladeExtensions);
    }

    protected static function registerRoleBladeExtensions(BladeCompiler $bladeCompiler)
    {
        // role
        $bladeCompiler->directive('role', static function (string $arguments): string {
            return sprintf(
                '<?php if (Auth::check() && Auth::user()->hasRole(%s)): ?>',
                $arguments
            );
        });

        $bladeCompiler->directive('endrole', static function (): string {
            return '<?php endif; ?>';
        });
    }

    protected static function registerUnlessRoleBladeExtensions(BladeCompiler $bladeCompiler)
    {
        // unlessrole
        $bladeCompiler->directive('unlessrole', static function (string $arguments): string {
            return sprintf(
                '<?php if (Auth::check() && !Auth::user()->hasRole(%s)): ?>',
                $arguments
            );
        });

        $bladeCompiler->directive('endunlessrole', static function () {
            return '<?php endif; ?>';
        });
    }

    protected static function registerPermissionBladeExtensions(BladeCompiler $bladeCompiler)
    {
        // permission
        $bladeCompiler->directive('permission', static function (string $arguments): string {
            return sprintf(
                '<?php if (Auth::check() && Auth::user()->hasPermissionTo(explode(\'|\', %s))): ?>',
                $arguments
            );
        });

        $bladeCompiler->directive('endpermission', static function (): string {
            return '<?php endif; ?>';
        });
    }

    protected static function registerAnyPermissionBladeExtensions(BladeCompiler $bladeCompiler)
    {
        // anyPermission
        $bladeCompiler->directive('anypermission', static function (string $arguments): string {
            return sprintf(
                '<?php if (Auth::check() && Auth::user()->hasAnyPermission(explode(\'|\', %s))): ?>',
                $arguments
            );
        });

        $bladeCompiler->directive('endanypermission', static function (): string {
            return '<?php endif; ?>';
        });
    }
}

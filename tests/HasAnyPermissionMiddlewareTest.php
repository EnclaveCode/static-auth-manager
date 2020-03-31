<?php

namespace EnclaveCode\StaticAuthManager\Test;

use EnclaveCode\StaticAuthManager\Middleware\HasAnyPermissionMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HasAnyPermissionMiddlewareTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasAnyPermissionMiddleware = new HasAnyPermissionMiddleware($this->app);

        $this->app['config']->set('permission.roles.admin', [
            'user/*',
        ]);
        $this->app['config']->set('permission.roles.user', [
            'article/*',
        ]);

        $this->user = User::create(['email' => 'test@user.com']);
    }

    protected function runMiddleware(HasAnyPermissionMiddleware $middleware, string $parameter): int
    {
        try {
            return $middleware->handle(new Request, static function () {
                return (new Response)->SetContent('<html></html>');
            }, $parameter)->status();
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }

    /** @test */
    public function without_permissions_cannot_access_protected_route(): void
    {
        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create'), 403);
    }

    /** @test */
    public function check_if_has_access_to_permission_if_has_this_permission(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);


        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create'), 200);
    }

    /** @test */
    public function check_if_has_access_to_one_permission_if_has_not_this_permission(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'article/create'), 403);
    }

    /** @test */
    public function check_if_has_access_to_one_permission_if_assign_one_permissions(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create'), 200);
    }

    /** @test */
    public function check_if_has_access_to_one_permission_if_assign_many_permissions(): void
    {
        $this->user->assignRole(['admin', 'user']);
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create'), 200);
    }
    /** @test */
    public function check_if_has_access_to_many_permissions_if_assign_many_permissions(): void
    {
        $this->user->assignRole(['admin', 'user']);
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create|article/edit'), 200);
    }
    /** @test */
    public function check_if_has_access_to_many_permission_if_assign_one_permissions(): void
    {
        $this->user->assignRole('user');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'user/create|article/edit'), 200);
    }

    /** @test */
    public function check_if_has_access_to_one_not_existed_permission(): void
    {
        $this->user->assignRole('user');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'foo/bar'), 403);
    }

    /** @test */
    public function check_if_has_access_to_many_not_existed_permission(): void
    {
        $this->user->assignRole('user');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasAnyPermissionMiddleware, 'foo/bar|bar/foo'), 403);
    }
}

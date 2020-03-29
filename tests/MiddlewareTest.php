<?php

namespace Enclave\StaticAuthManager\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Enclave\StaticAuthManager\Middlewares\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MiddlewareTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware($this->app);

        $this->app['config']->set('permission.roles.admin', [
            'users/*',
        ]);
        $this->app['config']->set('permission.roles.user', [
            'article/*',
        ]);

        $this->user = User::create(['email' => 'test@user.com']);
    }

    protected function runMiddleware(RoleMiddleware $middleware, string $parameter): int
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
    public function gust_cannot_access_protected_route(): void
    {
        $this->assertEquals($this->runMiddleware($this->roleMiddleware, 'admin'), 403);
    }

    /** @test */
    public function user_can_access_role_if_have_this_role(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->roleMiddleware, 'admin'), 200);
    }

    /** @test */
    public function user_can_access_role_if_have_this_roles(): void
    {
        $this->user->assignRole(['admin', 'user']);
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->roleMiddleware, 'admin|user'), 200);
    }

    /** @test */
    public function user_cant_access_role_if_have_not_role(): void
    {
        $incorrectRoleName = 'testRole';
        $this->expectIncorrectRoleNameException($incorrectRoleName);

        $this->user->assignRole('testRole');

        $this->assertEquals($this->runMiddleware($this->roleMiddleware, 'admin'), 403);
    }
}

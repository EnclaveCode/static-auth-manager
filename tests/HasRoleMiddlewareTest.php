<?php

namespace EnclaveCode\StaticAuthManager\Test;

use EnclaveCode\StaticAuthManager\Middlewares\HasRoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HasRoleMiddlewareTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasRoleMiddleware = new HasRoleMiddleware($this->app);

        $this->app['config']->set('permission.roles.admin', [
            'users/*',
        ]);
        $this->app['config']->set('permission.roles.user', [
            'article/*',
        ]);

        $this->user = User::create(['email' => 'test@user.com']);
    }

    protected function runMiddleware(HasRoleMiddleware $middleware, string $parameter): int
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
        $this->assertEquals($this->runMiddleware($this->hasRoleMiddleware, 'admin'), 403);
    }

    /** @test */
    public function user_can_access_role_if_have_this_role(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasRoleMiddleware, 'admin'), 200);
    }

    /** @test */
    public function user_can_access_role_if_have_this_roles(): void
    {
        $this->user->assignRole('admin');
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasRoleMiddleware, 'admin|user'), 200);
    }

    /** @test */
    public function user_can_access_role_if_have_many_roles(): void
    {
        $this->user->assignRole(['admin', 'user']);
        Auth::login($this->user);

        $this->assertEquals($this->runMiddleware($this->hasRoleMiddleware, 'admin|user'), 200);
    }

    /** @test */
    public function user_cant_access_role_if_have_not_role(): void
    {
        $incorrectRoleName = 'testRole';
        $this->expectIncorrectRoleNameException($incorrectRoleName);

        $this->user->assignRole('testRole');

        $this->assertEquals($this->runMiddleware($this->hasRoleMiddleware, 'admin'), 403);
    }
}

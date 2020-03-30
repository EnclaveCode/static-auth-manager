<?php

namespace EnclaveCode\StaticAuthManager\Test;


/**
 * Class PermissionTest
 */
class PermissionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPermissions = [
            'user/edit',
            'wildcard_example/*',
            'article/edit'
        ];
        $this->app['config']->set('permission.roles.user', $this->userPermissions);


        $this->adminPermissions = [
            'user/*',
            'article/create'
        ];
        $this->app['config']->set('permission.roles.admin', $this->adminPermissions);


        $this->user = User::create(['email' => 'test@user.com']);
    }

    /** @test */
    public function get_permissions_with_single_role(): void
    {
        $this->user->assignRole('user');

        $this->assertEquals($this->user->getPermissions(), collect($this->userPermissions));
    }

    /** @test */
    public function get_permissions_with_many_roles(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $adminPermissions = collect($this->adminPermissions);
        $userPermissions = collect($this->userPermissions);

        $allRolesPermissions = $adminPermissions->merge($userPermissions)->values()->sort();

        $this->assertEquals($this->user->getPermissions(), collect($allRolesPermissions));
    }

    /** @test */
    public function get_permissions_without_role(): void
    {
        $this->assertEquals($this->user->getPermissions(), collect([]));
    }

    /** @test */
    public function has_any_permission_string(): void
    {
        $this->user->assignRole('user');

        $this->assertTrue($this->user->hasAnyPermission('user/edit'));
    }

    /** @test */
    public function has_any_permission_array(): void
    {
        $this->user->assignRole('user');

        $this->assertTrue($this->user->hasAnyPermission(['user/edit', 'user/create']));
    }

    /** @test */
    public function has_permission_to_string(): void
    {
        $this->user->assignRole('user');

        $this->assertTrue($this->user->hasPermissionTo('user/edit'));
    }

    /** @test */
    public function test_has_permission_to_array(): void
    {
        $this->user->assignRole('user');

        $this->assertFalse($this->user->hasPermissionTo(['user/edit', 'user/create']));
    }

    /** @test */
    public function has_permission_to_forbidden_rule(): void
    {
        $this->user->assignRole('user');

        $this->assertFalse($this->user->hasPermissionTo('user/create'));
    }

    /** @test */
    public function has_permission_to_not_defined(): void
    {
        $this->user->assignRole('user');

        $this->assertFalse($this->user->hasPermissionTo('news/edit'));
    }

    /** @test */
    public function has_permission_to_multiple_wildcards(): void
    {
        $this->user->assignRole('user');

        $this->assertTrue($this->user->hasPermissionTo('wildcard_example/foo/bar'));
    }

    /** @test */
    public function has_permission_to_not_defined_with_multiple_roles(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $this->assertFalse($this->user->hasPermissionTo('news/edit'));
    }

    /** @test */
    public function has_permission_to_multiple_wildcards_with_multiple_roles(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $this->assertTrue($this->user->hasPermissionTo('wildcard_example/foo/bar'));
    }

    /** @test */
    public function has_permission_if_one_role_has_and_one_role_has_not_permission(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $this->assertTrue($this->user->hasPermissionTo('user/edit'));
    }

    /** @test */
    public function has_permission_if_every_role_has_different_permission(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $this->assertTrue($this->user->hasPermissionTo(['article/create', 'article/edit']));
    }

    /** @test */
    public function has_any_permission_if_every_role_has_different_permission(): void
    {
        $this->user->assignRole(['user', 'admin']);

        $this->assertFalse($this->user->hasPermissionTo(['article/create', 'article/foo']));
        $this->assertFalse($this->user->hasPermissionTo(['article/edit', 'article/foo']));
    }
}

<?php

namespace EnclaveCode\StaticAuthManager\Test;

class RoleTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('permission.roles.admin', [
            'users/*',
        ]);

        $this->app['config']->set('permission.roles.user', []);
        $this->app['config']->set('permission.roles.moderator', []);
        $this->app['config']->set('permission.roles.writer', []);


        $this->user = User::create(['email' => 'test@user.com']);
    }

    /** @test */
    public function assign_existent_one_role(): void
    {
        $role = 'admin';
        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));
    }

    /** @test */
    public function assign_existent_one_role_and_check_if_json_and_correct_structure(): void
    {
        $role = 'admin';
        $this->user->assignRole($role);

        $roleJson = $this->user->{config('permission.column_name')};
        $this->assertJson($roleJson);

        $decodedRole = json_decode($roleJson, true);
        $this->assertTrue(collect($decodedRole)->contains('admin'));
    }

    /** @test */
    public function assign_existent_many_role_and_check_if_json_and_correct_structure(): void
    {
        $role = ['admin', 'user'];
        $this->user->assignRole($role);

        $roleJson = $this->user->{config('permission.column_name')};
        $this->assertJson($roleJson);

        $decodedRole = json_decode($roleJson, true);
        $this->assertTrue(collect($decodedRole)->contains('admin'));
        $this->assertTrue(collect($decodedRole)->contains('user'));
    }

    /** @test */
    public function assign_existent_many_role(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));
    }

    /** @test */
    public function assign_non_existent_role(): void
    {
        $roleNotExisted = 'foo';

        $this->expectIncorrectRoleNameException($roleNotExisted);

        $this->user->assignRole($roleNotExisted);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function assign_non_existent_roles(): void
    {
        $rolesNotExisted = ['foo', 'bar'];

        $this->expectIncorrectRoleNameException($rolesNotExisted);

        $this->user->assignRole($rolesNotExisted);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function assign_one_existed_and_one_non_existent_roles(): void
    {
        $roleExisted = 'admin';
        $roleNotExisted = 'bar';

        $roles[] = $roleExisted;
        $roles[] = $roleNotExisted;

        $this->expectIncorrectRoleNameException($roleNotExisted);

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function assigned_two_existed_roles_but_one_already_assigned(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->assignRole('admin');

        $this->assertEquals($this->user->getRoles()->values(), collect($roles)->values());
    }

    /** @test */
    public function assigned_two_existed_roles_and_one_new(): void
    {
        $assignedRoles = ['admin', 'user'];
        $newRole = 'writer';

        $roles = $assignedRoles;
        $roles[] = $newRole;

        $this->user->assignRole($assignedRoles);

        $this->assertEquals($this->user->getRoles()->values(), collect($assignedRoles)->values());

        $this->user->assignRole($newRole);

        $this->assertEquals($this->user->getRoles()->values(), collect($roles)->values());
    }

    /** @test */
    public function has_not_existed_role(): void
    {
        $role = 'admin';
        $notExistedRole = 'foo';

        $this->expectIncorrectRoleNameException($notExistedRole);

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($notExistedRole));
        $this->assertTrue($this->user->hasRole($role));
    }

    /** @test */
    public function has_not_assigned_one_searched_role_in_assigned_one_role(): void
    {
        $role = 'admin';
        $searchedRole = 'moderator';

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_not_assigned_one_searched_role_in_assigned_many_roles(): void
    {
        $role = ['admin', 'user'];
        $searchedRole = 'moderator';

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_not_assigned_many_searched_roles_in_assigned_one_roles(): void
    {
        $role = 'admin';
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_not_assigned_many_searched_roles_in_assigned_many_roles(): void
    {
        $role = ['admin', 'writer'];
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_assigned_one_searched_role_in_assigned_many_roles(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertTrue($this->user->hasRole('user'));
    }

    /** @test */
    public function has_assigned_many_searched_role_in_assigned_many_roles(): void
    {
        $roles = ['admin', 'user'];
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_assigned_many_searched_role_in_assigned_one_role(): void
    {
        $roles = 'user';
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function has_assigned_one_searched_role_in_assigned_one_role(): void
    {
        $role = 'user';
        $searchedRole = 'user';

        $this->user->assignRole($role);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    /** @test */
    public function detach_one_role_if_has_one_role(): void
    {
        $role = 'user';
        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles()->values(), collect($role)->values());

        $this->user->detachRole($role);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function detach_many_roles_with_one_existed_if_has_one_role(): void
    {
        $role = 'user';

        $detachedRoles = ['user', 'admin'];

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function detach_many_roles_with_one_existed_if_has_many_roles(): void
    {
        $roles = ['user', 'writer'];

        $detachedRoles = ['user', 'admin'];

        $rolesDiff = array_diff($roles, $detachedRoles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles()->values(), collect($rolesDiff)->values());
    }

    /** @test */
    public function detach_many_roles_with_all_existed_if_has_many_roles(): void
    {
        $role = ['user', 'writer'];

        $detachedRoles = ['user', 'writer'];

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    /** @test */
    public function detach_many_roles_with_two_existed_if_has_many_roles(): void
    {
        $role = ['user', 'writer', 'admin'];

        $detachedRoles = ['user', 'admin'];

        $rolesDiff = array_diff($role, $detachedRoles);

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles(), collect($rolesDiff)->values());
    }

    /** @test */
    public function detach_one_roles_if_has_many_role(): void
    {
        $role = ['user', 'writer', 'admin'];

        $detachedRoles = 'writer';

        $rolesDiff = array_diff($role, [$detachedRoles]);

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles(), collect($rolesDiff)->sort()->values());
    }

    /** @test */
    public function detach_one_non_existed_role(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = 'foo';

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->expectIncorrectRoleNameException($detachedRoles);

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->hasRole($roles));
    }

    /** @test */
    public function detach_many_non_existed_role(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['foo', 'boom'];

        $rolesDiff = array_diff($detachedRoles, $roles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->user->detachRole($detachedRoles);


        $this->assertTrue($this->user->hasRole($roles));
    }

    /** @test */
    public function detach_many_non_existed_and_one_existed_role(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['writer', 'foo', 'boom'];

        $rolesDiff = array_diff($detachedRoles, $roles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->hasRole($roles));
    }

    /** @test */
    public function detach_many_non_existed_and_one_existed_role_another_case(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['foo', 'boom', 'writer'];

        $rolesDiff = array_diff($detachedRoles, $roles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->hasRole($roles));
    }

    /** @test */
    public function not_has_roles_when_user_does_not_have_any(): void
    {
        $this->assertTrue($this->user->getRoles()->isEmpty());
    }
}

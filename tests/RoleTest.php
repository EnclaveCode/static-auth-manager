<?php

namespace Enclave\StaticAuthManager\Test;

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

    public function testAssignExistentOneRole(): void
    {
        $role = 'admin';
        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));
    }

    public function testAssignExistentManyRole(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole();

        $this->assertEquals($this->user->getRoles(), collect($roles));
    }

    public function testAssignNonExistentRole(): void
    {
        $roleNotExisted = 'foo';

        $this->user->assignRole($roleNotExisted);

        $this->expectIncorrectRoleNameException($roleNotExisted);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testAssignNonExistentRoles(): void
    {
        $rolesNotExisted = ['foo', 'bar'];

        $this->user->assignRole($rolesNotExisted);

        $this->expectIncorrectRoleNameException($rolesNotExisted);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testAssignOneExistedAndOneNonExistentRoles(): void
    {
        $roleExisted = 'admin';
        $roleNotExisted = 'bar';

        $roles[] = $roleExisted;
        $roles[] = $roleNotExisted;

        $this->user->assignRole($roles);

        $this->expectIncorrectRoleNameException($roleNotExisted);

        $this->assertEquals($this->user->getRoles(), collect($roles));
    }

    public function testAssignedTwoExistedRolesButOneAlreadyAssigned(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->assignRole('admin');

        $this->assertEquals($this->user->getRoles(), collect($roles));
    }

    public function testHasNotExistedRole(): void
    {
        $role = 'admin';
        $notExistedRole = 'foo';

        $this->user->assignRole($role);

        $this->expectIncorrectRoleNameException($notExistedRole);

        $this->assertFalse($this->user->hasRole($notExistedRole));
    }

    public function testHasNotAssignedOneSearchedRoleInAssignedOneRole(): void
    {
        $role = 'admin';
        $searchedRole = 'moderator';

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }
    public function testHasNotAssignedOneSearchedRoleInAssignedManyRoles(): void
    {
        $role = ['admin', 'users'];
        $searchedRole = 'moderator';

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    public function testHasNotAssignedManySearchedRolesInAssignedOneRoles(): void
    {
        $role = 'admin';
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    public function testHasNotAssignedManySearchedRolesInAssignedManyRoles(): void
    {
        $role = ['admin', 'writer'];
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($searchedRole));
    }

    public function testHasAssignedOneSearchedRoleInAssignedManyRoles(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertTrue($this->user->hasRole('user'));
    }

    public function testHasAssignedManySearchedRoleInAssignedManyRoles(): void
    {
        $roles = ['admin', 'user'];
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    public function testHasAssignedManySearchedRoleInAssignedOneRole(): void
    {
        $roles = 'user';
        $searchedRole = ['user', 'moderator'];

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    public function testHasAssignedOneSearchedRoleInAssignedOneRole(): void
    {
        $role = 'user';
        $searchedRole = 'user';

        $this->user->assignRole($role);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    public function testDetachOneRoleIfHasOneRole(): void
    {
        $role = 'user';
        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($role);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testDetachManyRolesWithOneExistedIfHasOneRole(): void
    {
        $role = 'user';

        $detachedRoles = ['user', 'admin'];

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testDetachManyRolesWithOneExistedIfHasManyRole(): void
    {
        $role = ['user', 'writer'];

        $detachedRoles = ['user', 'admin'];

        $rolesDiff = array_diff($role, $detachedRoles);

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles(), collect($rolesDiff));
    }

    public function testDetachManyRolesWithTwoExistedIfHasManyRole(): void
    {
        $role = ['user', 'writer', 'admin'];

        $detachedRoles = ['user', 'admin'];

        $rolesDiff = array_diff($role, $detachedRoles);

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles(), collect($rolesDiff));
    }

    public function testDetachOneRolesIfHasManyRole(): void
    {
        $role = ['user', 'writer', 'admin'];

        $detachedRoles = 'writer';

        $rolesDiff = array_diff($role, [$detachedRoles]);

        $this->user->assignRole($role);

        $this->assertEquals($this->user->getRoles(), collect($role));

        $this->user->detachRole($detachedRoles);

        $this->assertEquals($this->user->getRoles(), collect($rolesDiff));
    }

    public function testDetachOneNonExistedRole(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = 'foo';

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->detachRole($detachedRoles);

        $this->expectIncorrectRoleNameException($detachedRoles);

        $this->assertTrue($this->user->hasRole($roles));
    }

    public function testDetachManyNonExistedRole(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['foo', 'boom'];

        $rolesDiff = array_diff($roles, $detachedRoles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->detachRole($detachedRoles);

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->assertTrue($this->user->hasRole($roles));
    }

    public function testDetachManyNonExistedAndOneExistedRole(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['writer', 'foo', 'boom'];

        $rolesDiff = array_diff($roles, $detachedRoles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->detachRole($detachedRoles);

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->assertTrue($this->user->hasRole($roles));
    }

    public function testDetachManyNonExistedAndOneExistedRoleAnotherCase(): void
    {
        $roles = ['user', 'writer', 'admin'];

        $detachedRoles = ['foo', 'boom', 'writer'];

        $rolesDiff = array_diff($roles, $detachedRoles);

        $this->user->assignRole($roles);

        $this->assertEquals($this->user->getRoles(), collect($roles));

        $this->user->detachRole($detachedRoles);

        $this->expectIncorrectRoleNameException($rolesDiff);

        $this->assertTrue($this->user->hasRole($roles));
    }

    public function testNotHasRolesWhenUserDoesNotHaveAny(): void
    {
        $this->assertTrue($this->user->getRoles()->isEmpty());
    }
}

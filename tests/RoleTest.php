<?php

namespace Enclave\StaticAuthManager\Test;

use Enclave\StaticAuthManager\Exceptions\IncorrectRoleNameException;

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

        $this->expectException(IncorrectRoleNameException::class);
        $this->expectExceptionMessage('Role: ' . collect($roleNotExisted)->toJson() . 'does not exist');


        $this->$this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testAssignNonExistentRoles(): void
    {
        $rolesNotExisted = ['foo', 'bar'];

        $this->user->assignRole($rolesNotExisted);

        $this->expectException(IncorrectRoleNameException::class);
        $this->expectExceptionMessage('Role: ' . collect($rolesNotExisted)->toJson() . 'does not exist');

        $this->$this->assertTrue($this->user->getRoles()->isEmpty());
    }

    public function testAssignOneExistedAndOneNonExistentRoles(): void
    {
        $roleExisted = 'admin';
        $rolesNotExisted = 'bar';

        $roles[] = $roleExisted;
        $roles[] = $roleNotExisted;

        $this->user->assignRole($roles);

        $this->expectException(IncorrectRoleNameException::class);
        $this->expectExceptionMessage('Role: ' . collect($rolesNotExisted)->toJson() . 'does not exist');


        $this->$this->assertEquals($this->user->getRoles()->isEmpty(), collect($roles));
    }

    public function testAssignedTwoExistedRolesButOneAlreadyAssigned(): void
    {
        $roles = ['admin', 'user'];

        $this->user->assignRole($roles);

        $this->$this->assertTrue($this->user->getRoles(), collect($roles));

        $this->user->assignRole('admin');

        $this->$this->assertTrue($this->user->getRoles(), collect($roles));
    }

    public function testHasNotExistedRole(): void
    {
        $role = 'admin';
        $notExistedRole = 'foo';

        $this->user->assignRole($role);

        $this->assertFalse($this->user->hasRole($notExistedRole));

        $this->expectException(IncorrectRoleNameException::class);
        $this->expectExceptionMessage('Role: ' . collect($notExistedRole)->toJson() . 'does not exist');
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
        $roles = 'user';
        $searchedRole = 'user';

        $this->user->assignRole($roles);

        $this->assertTrue($this->user->hasRole($searchedRole));
    }

    public function testGetAllRolesWhenUserDoesNotHaveAny(): void
    {
        $this->assertEquals($this->user->getRoles(), collect());
    }
}

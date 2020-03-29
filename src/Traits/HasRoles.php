<?php

namespace Enclave\StaticAuthManager\Traits;

use Enclave\StaticAuthManager\Exceptions\IncorrectRoleNameException;
use Illuminate\Support\Collection;

trait HasRoles
{

    use HasPermissions;

    /**
     * Assign the role to the model
     *
     * @param  array|string $roles role name
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten();

        $this->checkIfRolesExistsInConfig($roles);

        $currentRoles = $this->getRoles();

        $concatedRoles = $roles->concat($currentRoles)->unique();

        $this->addRolesToModel($concatedRoles);

        return $this;
    }

    private function checkIfRolesExistsInConfig(Collection $roles)
    {
        $rolesInConfig = $this->getAllRolesInConfig();

        $rolesDiff = $roles->diff($rolesInConfig);

        if ($rolesDiff->count() >= 1) {
            throw new IncorrectRoleNameException('Role: ' . collect($rolesDiff)->flatten()->toJson() . ' does not exist');
        }
    }

    private function getAllRolesInConfig()
    {
        return collect(config('permission.roles'))->keys();
    }

    private function addRolesToModel(Collection $roles)
    {
        $this->{config('permission.column_name')} = $roles->values()->toJson();

        $this->save();
    }

    /**
     * Compare role with given model role
     *
     * @param  array|string  $role role name
     * @return bool
     */
    public function hasRole(...$roles): bool
    {
        $currentRoles = $this->getRoles();
        $roles = collect($roles)->flatten();

        $this->checkIfRolesExistsInConfig($roles);

        return $roles
            ->flatten()
            ->values()
            ->sort()
            ->filter(function ($role) use ($currentRoles) {
                return $currentRoles->contains($role);
            })
            ->count() >= 1;
    }

    /**
     * Return current model permissions
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoles(): Collection
    {
        $currentRolesJson = $this->{config('permission.column_name')};
        $currentRoles = json_decode($currentRolesJson);
        $roles = collect($currentRoles)
            ->values()
            ->sort();

        return $roles;
    }

    /**
     * Detach the role from the model
     *
     * @param  array|string $roles role name
     * @return $this
     */
    public function detachRole(...$roles)
    {

        $roles = collect($roles)
            ->flatten();

        $this->checkIfRolesExistsInConfig($roles);

        $currentRoles = $this->getRoles();

        $concatedRoles = $currentRoles->filter(function ($role) use ($roles) {
            return !$roles->contains($role);
        });

        $this->addRolesToModel($concatedRoles);

        return $this;
    }
}

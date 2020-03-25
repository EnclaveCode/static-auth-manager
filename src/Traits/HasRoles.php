<?php

namespace Enclave\StaticAuthManager\Traits;

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

        $rolesInConfig = config('permission.roles');
        $roles = collect($roles)
            ->flatten();



        return $this;
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
        $roles = collect($roles);

        return $currentRoles
            ->flatten()
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
        $roles = collect($currentRoles);

        return $roles;
    }

    //@TODO - DETACH ROLE
}

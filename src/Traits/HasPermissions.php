<?php

namespace Enclave\StaticAuthManager\Traits;

use Illuminate\Support\Collection;

trait HasPermissions
{

    /**
     * Check if model has all permissions
     *
     * @param  array|string  ...$permissions
     * @return bool
     */
    public function hasPermissionTo(...$permissions): bool
    {
        $rules = $this->getPermissions()
            ->map(static function (string $rule) {
                return explode('/', $rule);
            });

        $permissions = collect($permissions)
            ->flatten();

        return $permissions
            ->map(static function (string $permission) {
                return explode('/', $permission);
            })
            ->filter(function (array $permission) use ($rules) {
                return $this->matchPermission($rules, $permission);
            })->count() === $permissions->count();
    }

    /**
     * Check if model has any permissions
     *
     * @param  array|string  ...$permissions
     * @return bool
     */
    public function hasAnyPermission(...$permissions): bool
    {
        $rules = $this->getPermissions()
            ->map(static function (string $rule) {
                return explode('/', $rule);
            });

        return collect($permissions)
            ->flatten()
            ->map(static function (string $permission) {
                return explode('/', $permission);
            })
            ->filter(function (array $permission) use ($rules) {
                return $this->matchPermission($rules, $permission);
            })->count() > 0;
    }

    /**
     * Return current model permissions
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(): Collection
    {
        //@TODO - UWZGLĘDNIĆ ROLE
        $roles = collect(config('permission.roles'));
        $actualRoles = $this->getRoles();

        return $actualRoles
            ->map(function ($role) use ($roles) {
                return $roles->get($role);
            })
            ->flatten()
            ->unique();
    }

    /**
     * Match ruleset to permission
     *
     * @param  \Illuminate\Support\Collection $rules Ruleset
     * @param  array  $permission
     * @return bool
     */
    public function matchPermission(Collection $rules, array $permission): bool
    {
        // match rules
        $matches = $rules->filter(function ($rule) use ($permission) {
            return $this->matchRuleToPermission($rule, $permission);
        });

        // match positive rule
        return $matches->count() > 0;
    }

    /**
     * Match one rule to permission
     *
     * @param  array $rule
     * @param  array $permission
     * @return bool
     */
    public function matchRuleToPermission(array $rule, array $permission): bool
    {

        $countRuleParts = count($rule);
        $countPermissionParts = count($permission);

        for ($i = 0; $i < $countPermissionParts; $i++) {
            // topic is longer, and no wildcard
            if ($i >= $countRuleParts) {
                return false;
            }

            // matched up to here, and now the wildcard says "all others will match"
            if ($rule[$i] === '*') {
                return true;
            }

            // text does not match
            if ($permission[$i] !== $rule[$i]) {
                return false;
            }
        }

        // make user/edit/# match user/edit
        if ($countPermissionParts === $countRuleParts - 1 && $rule[$countRuleParts - 1] === '*') {
            return true;
        }

        return $countPermissionParts === $countRuleParts;
    }
}

<?php

namespace Imanghafoori\HeyMan\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Imanghafoori\HeyMan\Exceptions\GuardDoesNotMatch;
use Imanghafoori\HeyMan\Utils\GuardManager;

trait HasRoles
{
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->all();

        $this->roles()->saveMany($roles);

        return $this;
    }

    protected function getStoredRole($role): Role
    {
        if (is_numeric($role)) {
            return app(Role::class)->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return app(Role::class)->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            Role::class,
            'model',
            config('heyMan.table_names.model_has_roles'),
            'model_id',
            'role_id'
        );
    }

    protected function getGuardNames(): Collection
    {
        return GuardManager::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return GuardManager::getDefaultName($this);
    }

    protected function ensureModelSharesGuard($roleOrPermission)
    {
        if (! $this->getGuardNames()->contains($roleOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create($roleOrPermission->guard_name, $this->getGuardNames());
        }
    }

}
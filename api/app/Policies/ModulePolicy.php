<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy co so: anh xa cac ability CRUD sang permission {module}.{action}.
 * Super Admin da duoc Gate::before cho phep tat ca.
 */
abstract class ModulePolicy
{
    /** Ten module, vd: products */
    protected string $module = '';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module.'.view');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasPermission($this->module.'.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module.'.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasPermission($this->module.'.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasPermission($this->module.'.delete');
    }

    /** Xuat ban / duyet hien thi. */
    public function publish(User $user, mixed $model = null): bool
    {
        return $user->hasPermission($this->module.'.publish');
    }
}

<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/** Shared authorization rules; keep the API and CMS copies identical. */
class UserAccess
{
    public static function canAssignRole(User $actor, Role $role): bool
    {
        return $actor->hasRole('super-admin') || ($role->name !== 'super-admin'
            && $role->permissions->every(fn ($permission) => $actor->hasPermission($permission->name)));
    }

    public static function assertManage(User $actor, User $target): void
    {
        foreach ($target->roles()->with('permissions')->get() as $role) {
            if (! self::canAssignRole($actor, $role)) {
                throw new AuthorizationException('Bạn không được quản lý tài khoản có quyền cao hơn mình.');
            }
        }
    }

    public static function assertRoles(User $actor, array $roleIds, ?User $target = null): void
    {
        foreach (Role::with('permissions')->whereIn('id', $roleIds)->get() as $role) {
            if (! self::canAssignRole($actor, $role)) {
                throw new AuthorizationException('Bạn không được cấp vai trò có quyền cao hơn mình.');
            }
        }
        if ($target?->hasRole('super-admin')) {
            $superId = Role::where('name', 'super-admin')->value('id');
            if (! in_array($superId, $roleIds)) {
                if ($target->id === $actor->id || User::whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->count() <= 1) {
                    throw ValidationException::withMessages(['roles' => 'Không thể gỡ Super Admin của chính mình hoặc Super Admin cuối cùng.']);
                }
            }
        }
    }

    public static function assertDelete(User $actor, User $target): void
    {
        self::assertManage($actor, $target);
        if ($actor->id === $target->id || ($target->hasRole('super-admin')
            && User::whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->count() <= 1)) {
            throw ValidationException::withMessages(['user' => 'Không thể xóa chính mình hoặc Super Admin cuối cùng.']);
        }
    }

    public static function assertRolePermissions(User $actor, array $permissionIds, ?Role $role = null): void
    {
        if ($actor->hasRole('super-admin')) {
            return;
        }
        if ($role && ! self::canAssignRole($actor, $role)) {
            throw new AuthorizationException('Bạn không được sửa vai trò có quyền cao hơn mình.');
        }
        foreach (Permission::whereIn('id', $permissionIds)->get() as $permission) {
            if (! $actor->hasPermission($permission->name)) {
                throw new AuthorizationException('Bạn không được cấp quyền mà mình không có.');
            }
        }
    }
}

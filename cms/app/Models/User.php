<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class, 'created_by');
    }

    public function hasRole(string $roleName): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains('name', $roleName);
    }

    /**
     * Kiểm tra quyền. Vai trò super-admin luôn có mọi quyền.
     */
    public function hasPermission(string $permissionName): bool
    {
        $this->loadMissing('roles.permissions');

        foreach ($this->roles as $role) {
            if ($role->name === 'super-admin') {
                return true;
            }

            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function revokeApiTokens(): void
    {
        \Illuminate\Support\Facades\DB::table('personal_access_tokens')
            ->where('tokenable_type', $this->getMorphClass())
            ->where('tokenable_id', $this->id)->delete();
    }
}

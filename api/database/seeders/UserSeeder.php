<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // TAI KHOAN SEED MAC DINH — DOI MAT KHAU NGAY SAU KHI DANG NHAP LAN DAU.
        // (co must_change_password = true, CMS se ep doi mat khau)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên',
                'password' => Hash::make('Admin123!'),
                'must_change_password' => true,
            ]
        );

        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin && ! $admin->roles()->where('role_id', $superAdmin->id)->exists()) {
            $admin->roles()->attach($superAdmin->id);
        }
    }
}

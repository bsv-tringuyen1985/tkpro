<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database theo dung thu tu phu thuoc (contract 2.2).
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PostSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            BannerSeeder::class,
            SettingSeeder::class,
        ]);
    }
}

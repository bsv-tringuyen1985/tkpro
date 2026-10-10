<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder của CMS để trống có chủ đích.
 *
 * Toàn bộ seed data (roles, permissions, admin user, danh mục, sản phẩm,
 * bài viết, trang, menu, banner, settings) do phía API sở hữu và seed,
 * vì api và cms dùng CHUNG một database `tkpro` (theo CONTRACTS.md mục 2.2).
 *
 * Chạy `php artisan db:seed` ở đây sẽ không làm gì cả — an toàn khi
 * migrate ở container nào cũng được.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Không seed ở CMS — dữ liệu seed do API quản lý.
    }
}

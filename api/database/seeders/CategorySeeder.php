<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $productCategories = [
            [
                'name' => 'Camera giám sát',
                'slug' => 'camera-giam-sat',
                'description' => 'Camera WiFi, camera IP, đầu ghi và phụ kiện giám sát an ninh cho gia đình, cửa hàng, văn phòng.',
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'name' => 'Linh kiện PC',
                'slug' => 'linh-kien-pc',
                'description' => 'VGA, SSD, RAM, nguồn, mainboard và linh kiện máy tính chính hãng.',
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'name' => 'Thiết bị điện tử',
                'slug' => 'thiet-bi-dien-tu',
                'description' => 'Router WiFi, màn hình, thiết bị mạng và đồ điện tử gia đình.',
                'status' => 'published',
                'sort_order' => 3,
            ],
        ];

        foreach ($productCategories as $data) {
            ProductCategory::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $postCategories = [
            [
                'name' => 'Khuyến mãi',
                'slug' => 'khuyen-mai',
                'description' => 'Chương trình khuyến mãi và ưu đãi mới nhất từ TKPRO.',
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'name' => 'PC Build',
                'slug' => 'pc-build',
                'description' => 'Tư vấn chọn cấu hình PC theo nhu cầu và ngân sách.',
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'name' => 'Hướng dẫn',
                'slug' => 'huong-dan',
                'description' => 'Hướng dẫn sử dụng, lắp đặt và nâng cấp thiết bị.',
                'status' => 'published',
                'sort_order' => 3,
            ],
        ];

        foreach ($postCategories as $data) {
            PostCategory::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}

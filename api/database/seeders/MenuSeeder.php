<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Menu header ----
        $header = Menu::firstOrCreate(
            ['slug' => 'header'],
            ['name' => 'Menu chính', 'location' => 'header']
        );

        $headerItems = [
            ['title' => 'Trang chủ', 'url' => '/', 'sort_order' => 1],
            ['title' => 'Sản phẩm', 'url' => '/san-pham', 'sort_order' => 2],
            ['title' => 'Camera', 'url' => '/san-pham/camera-giam-sat', 'sort_order' => 3],
            ['title' => 'Linh kiện PC', 'url' => '/san-pham/linh-kien-pc', 'sort_order' => 4],
            ['title' => 'Tin tức', 'url' => '/tin-tuc', 'sort_order' => 5],
            ['title' => 'Liên hệ', 'url' => '/lien-he', 'sort_order' => 6],
        ];

        foreach ($headerItems as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $header->id, 'title' => $item['title'], 'parent_id' => null],
                array_merge($item, ['menu_id' => $header->id, 'type' => 'url', 'target' => '_self', 'is_active' => true])
            );
        }

        // ---- Menu footer: 3 cot (San pham / Dich vu / TKPRO) ----
        $footer = Menu::firstOrCreate(
            ['slug' => 'footer'],
            ['name' => 'Menu chân trang', 'location' => 'footer']
        );

        $columns = [
            [
                'title' => 'Sản phẩm',
                'children' => [
                    ['title' => 'Camera giám sát', 'url' => '/san-pham/camera-giam-sat'],
                    ['title' => 'Linh kiện PC', 'url' => '/san-pham/linh-kien-pc'],
                    ['title' => 'Thiết bị điện tử', 'url' => '/san-pham/thiet-bi-dien-tu'],
                ],
            ],
            [
                'title' => 'Dịch vụ',
                'children' => [
                    ['title' => 'Lắp đặt camera', 'url' => '/dich-vu'],
                    ['title' => 'Build PC theo yêu cầu', 'url' => '/dich-vu'],
                    ['title' => 'Bảo trì hệ thống', 'url' => '/dich-vu'],
                ],
            ],
            [
                'title' => 'TKPRO',
                'children' => [
                    ['title' => 'Giới thiệu', 'url' => '/gioi-thieu'],
                    ['title' => 'Tin tức', 'url' => '/tin-tuc'],
                    ['title' => 'Liên hệ', 'url' => '/lien-he'],
                ],
            ],
        ];

        $sort = 1;
        foreach ($columns as $column) {
            $parent = MenuItem::firstOrCreate(
                ['menu_id' => $footer->id, 'title' => $column['title'], 'parent_id' => null],
                [
                    'menu_id' => $footer->id,
                    'type' => 'url',
                    'url' => '#',
                    'target' => '_self',
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            $childSort = 1;
            foreach ($column['children'] as $child) {
                MenuItem::firstOrCreate(
                    ['menu_id' => $footer->id, 'title' => $child['title'], 'parent_id' => $parent->id],
                    array_merge($child, [
                        'menu_id' => $footer->id,
                        'parent_id' => $parent->id,
                        'type' => 'url',
                        'target' => '_self',
                        'sort_order' => $childSort++,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::firstOrCreate(
            ['title' => 'Nâng cấp không gian, làm chủ công nghệ.'],
            [
                'subtitle' => 'Khám phá hệ sinh thái thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Tư vấn đúng nhu cầu, lắp đặt chuẩn và bảo hành rõ ràng.',
                'link' => '/san-pham',
                'button_text' => 'Xem sản phẩm',
                'sort_order' => 0,
                'is_active' => true,
            ]
        );
    }
}

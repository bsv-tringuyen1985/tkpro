<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // general
            ['key' => 'site_name', 'value' => 'TKPRO', 'group' => 'general'],
            ['key' => 'announcement', 'value' => 'Hàng chính hãng · Bảo hành tận tâm · Giao hàng toàn quốc', 'group' => 'general'],
            ['key' => 'copyright', 'value' => '© 2026 TKPRO · Điện tử · Camera · Linh kiện máy tính · Bảo hành chính hãng', 'group' => 'general'],
            ['key' => 'footer_about', 'value' => 'Thiết bị điện tử, camera và linh kiện máy tính chính hãng.', 'group' => 'general'],
            // contact
            ['key' => 'hotline', 'value' => '1900 6868', 'group' => 'contact'],
            ['key' => 'email', 'value' => 'support@techvision.vn', 'group' => 'contact'],
            ['key' => 'address', 'value' => '123 Nguyễn Trãi, TP.HCM', 'group' => 'contact'],
            ['key' => 'working_hours', 'value' => '08:00 — 21:00 mỗi ngày', 'group' => 'contact'],
            // social
            ['key' => 'facebook', 'value' => null, 'group' => 'social'],
            ['key' => 'youtube', 'value' => null, 'group' => 'social'],
            ['key' => 'zalo', 'value' => '#', 'group' => 'social'],
            // seo
            ['key' => 'google_maps_embed', 'value' => '', 'group' => 'seo'],
        ];

        foreach ($settings as $item) {
            Setting::updateOrCreate(['key' => $item['key']], $item);
        }
    }
}

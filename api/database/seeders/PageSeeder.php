<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Trang chủ',
                'slug' => 'trang-chu',
                'excerpt' => 'TKPRO — Thiết bị điện tử, camera và linh kiện máy tính chính hãng.',
                'content' => '<p><strong>TKPRO</strong> là điểm đến tin cậy cho thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Chúng tôi tư vấn đúng nhu cầu, lắp đặt chuẩn kỹ thuật và bảo hành rõ ràng.</p><p>Hàng chính hãng · Bảo hành tận tâm · Giao hàng toàn quốc.</p>',
                'sort_order' => 0,
            ],
            [
                'title' => 'Giới thiệu',
                'slug' => 'gioi-thieu',
                'excerpt' => 'Giới thiệu về TKPRO — đơn vị cung cấp thiết bị điện tử, camera và linh kiện máy tính chính hãng.',
                'content' => '<p><strong>TKPRO</strong> chuyên cung cấp thiết bị điện tử, camera giám sát và linh kiện máy tính chính hãng với phương châm: tư vấn đúng nhu cầu, lắp đặt chuẩn và bảo hành tận tâm.</p><p>Với đội ngũ kỹ thuật nhiều năm kinh nghiệm, chúng tôi đã đồng hành cùng hàng nghìn gia đình, cửa hàng và doanh nghiệp trong việc xây dựng hệ thống an ninh, nâng cấp máy tính và trang bị thiết bị công nghệ.</p><h3>Cam kết của chúng tôi</h3><ul><li>100% hàng chính hãng, nguồn gốc rõ ràng</li><li>Bảo hành nhanh chóng, minh bạch</li><li>Hỗ trợ kỹ thuật 24/7</li><li>Giao hàng toàn quốc</li></ul>',
                'sort_order' => 1,
            ],
            [
                'title' => 'Dịch vụ',
                'slug' => 'dich-vu',
                'excerpt' => 'Các dịch vụ của TKPRO: tư vấn, lắp đặt camera, build PC, bảo trì hệ thống.',
                'content' => '<p>TKPRO cung cấp trọn gói dịch vụ công nghệ cho gia đình và doanh nghiệp:</p><h3>Lắp đặt camera an ninh</h3><p>Khảo sát, tư vấn combo camera phù hợp, thi công gọn gàng và hướng dẫn sử dụng tận tình.</p><h3>Build PC theo yêu cầu</h3><p>Tư vấn cấu hình đúng nhu cầu và ngân sách: văn phòng, học tập, gaming hay đồ họa chuyên nghiệp. Lắp ráp chuẩn, đi dây gọn, cài đặt hoàn chỉnh.</p><h3>Bảo trì & nâng cấp</h3><p>Vệ sinh, nâng cấp SSD/RAM, kiểm tra hệ thống định kỳ giúp thiết bị luôn vận hành ổn định.</p><p>Liên hệ hotline <strong>1900 6868</strong> để được tư vấn miễn phí.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => 'Liên hệ',
                'slug' => 'lien-he',
                'excerpt' => 'Thông tin liên hệ TKPRO: hotline 1900 6868, email support@techvision.vn.',
                'content' => '<p><strong>TKPRO</strong></p><ul><li>Địa chỉ: 123 Nguyễn Trãi, TP.HCM</li><li>Hotline: 1900 6868</li><li>Email: support@techvision.vn</li><li>Giờ làm việc: 08:00 — 21:00 mỗi ngày</li></ul><p>Gửi tin nhắn qua form liên hệ, chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>',
                'sort_order' => 3,
            ],
        ];

        foreach ($pages as $item) {
            Page::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'status' => 'published',
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}

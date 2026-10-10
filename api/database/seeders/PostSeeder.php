<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $categories = PostCategory::pluck('id', 'slug');
        $authorId = User::where('email', 'admin@example.com')->value('id');

        $posts = [
            [
                'title' => 'Combo camera gia đình — bảo vệ ngôi nhà 24/7',
                'slug' => 'combo-camera-gia-dinh-bao-ve-ngoi-nha-24-7',
                'category' => 'khuyen-mai',
                'excerpt' => 'Gợi ý lựa chọn camera phù hợp cho căn hộ, nhà phố và cửa hàng.',
                'content' => 'Bài viết chi tiết về cách chọn combo camera cho gia đình: số lượng mắt camera, vị trí lắp đặt, dung lượng lưu trữ và những lưu ý khi thi công để hệ thống hoạt động ổn định 24/7.',
            ],
            [
                'title' => 'Chọn cấu hình PC đúng nhu cầu và ngân sách',
                'slug' => 'chon-cau-hinh-pc-dung-nhu-cau-va-ngan-sach',
                'category' => 'pc-build',
                'excerpt' => 'Từ văn phòng, học tập đến gaming và đồ họa chuyên nghiệp.',
                'content' => 'Hướng dẫn chọn cấu hình PC theo từng nhu cầu: văn phòng, học tập, gaming và đồ họa. Cách cân đối giữa CPU, VGA, RAM và SSD để có hiệu năng tốt nhất trong tầm giá.',
            ],
            [
                'title' => 'SSD NVMe, RAM và nguồn: nâng cấp gì trước?',
                'slug' => 'ssd-nvme-ram-va-nguon-nang-cap-gi-truoc',
                'category' => 'huong-dan',
                'excerpt' => 'Những nâng cấp mang lại hiệu quả rõ rệt cho máy tính.',
                'content' => 'Nếu máy tính chạy chậm, đâu là linh kiện nên nâng cấp trước? Bài viết phân tích hiệu quả của từng nâng cấp: SSD giúp khởi động nhanh, RAM giúp đa nhiệm mượt, nguồn đảm bảo ổn định lâu dài.',
            ],
        ];

        foreach ($posts as $item) {
            Post::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $categories[$item['category']] ?? null,
                    'user_id' => $authorId,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }
}

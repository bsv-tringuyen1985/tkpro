<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/** Dung 8 san pham theo contract 2.2 (featured = true cho 4 san pham dau). */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ProductCategory::pluck('id', 'slug');

        $products = [
            [
                'name' => 'Camera WiFi 2K trong nhà',
                'slug' => 'camera-wifi-2k-trong-nha',
                'sku' => 'CAM-WIFI-2K',
                'category' => 'camera-giam-sat',
                'price' => 890000,
                'featured' => true,
                'short_description' => 'Camera WiFi độ phân giải 2K, đàm thoại 2 chiều, quay đêm có màu.',
                'description' => 'Camera WiFi 2K trong nhà là lựa chọn lý tưởng để giám sát căn hộ, nhà phố và cửa hàng. Hình ảnh sắc nét cả ngày lẫn đêm, đàm thoại 2 chiều giúp bạn trò chuyện với người thân từ xa, cùng khả năng quay quét linh hoạt bao quát toàn bộ không gian.',
                'technical_specifications' => [
                    ['label' => 'Độ phân giải', 'value' => '2K QHD (2304×1296)'],
                    ['label' => 'Đàm thoại', 'value' => '2 chiều, micro và loa tích hợp'],
                    ['label' => 'Quay đêm', 'value' => 'Hồng ngoại có màu, tầm xa 10m'],
                    ['label' => 'Kết nối', 'value' => 'WiFi 2.4GHz, hỗ trợ app điện thoại'],
                    ['label' => 'Lưu trữ', 'value' => 'Thẻ nhớ microSD đến 128GB, cloud'],
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Camera IP AI ngoài trời 4MP',
                'slug' => 'camera-ip-ai-ngoai-troi-4mp',
                'sku' => 'CAM-IP-4MP',
                'category' => 'camera-giam-sat',
                'price' => 1690000,
                'featured' => true,
                'short_description' => 'Chống nước IP66, nhận diện người bằng AI, cảnh báo chuyển động.',
                'description' => 'Camera IP AI ngoài trời 4MP với khả năng nhận diện người bằng trí tuệ nhân tạo, giảm báo động giả do vật nuôi hay lá cây. Chuẩn chống nước IP66 hoạt động bền bỉ dưới mọi điều kiện thời tiết, cảnh báo chuyển động tức thì về điện thoại.',
                'technical_specifications' => [
                    ['label' => 'Độ phân giải', 'value' => '4MP (2560×1440)'],
                    ['label' => 'Chống nước', 'value' => 'IP66, dùng ngoài trời'],
                    ['label' => 'AI', 'value' => 'Nhận diện người, phương tiện'],
                    ['label' => 'Cảnh báo', 'value' => 'Đẩy thông báo, còi và đèn chớp'],
                    ['label' => 'Kết nối', 'value' => 'WiFi / LAN, hỗ trợ PoE'],
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'VGA RTX Gaming 4060 8GB',
                'slug' => 'vga-rtx-gaming-4060-8gb',
                'sku' => 'VGA-4060-8G',
                'category' => 'linh-kien-pc',
                'price' => 7990000,
                'featured' => true,
                'short_description' => 'Card đồ họa RTX 4060 8GB GDDR6, chiến mượt game AAA ở Full HD/2K.',
                'description' => 'Card đồ họa RTX 4060 8GB GDDR6 trên kiến trúc Ada Lovelace, hỗ trợ DLSS 3 tăng FPS vượt trội. Chiến mượt các tựa game AAA ở độ phân giải Full HD và 2K, đồng thời đáp ứng tốt nhu cầu đồ họa, render và AI.',
                'technical_specifications' => [
                    ['label' => 'GPU', 'value' => 'NVIDIA GeForce RTX 4060'],
                    ['label' => 'Bộ nhớ', 'value' => '8GB GDDR6, 128-bit'],
                    ['label' => 'Công nghệ', 'value' => 'DLSS 3, Ray Tracing thế hệ 3'],
                    ['label' => 'Cổng xuất hình', 'value' => 'HDMI 2.1, DisplayPort 1.4a'],
                    ['label' => 'Nguồn đề xuất', 'value' => '550W trở lên'],
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'SSD NVMe 1TB Gen4',
                'slug' => 'ssd-nvme-1tb-gen4',
                'sku' => 'SSD-1TB-G4',
                'category' => 'linh-kien-pc',
                'price' => 1590000,
                'featured' => true,
                'short_description' => 'Tốc độ đọc đến 7000MB/s, bảo hành 5 năm chính hãng.',
                'description' => 'SSD NVMe 1TB chuẩn PCIe Gen4 x4 cho tốc độ đọc ghi vượt trội, khởi động máy và mở ứng dụng chỉ trong vài giây. Nâng cấp đơn giản mà hiệu quả rõ rệt nhất cho mọi cấu hình máy tính.',
                'technical_specifications' => [
                    ['label' => 'Dung lượng', 'value' => '1TB'],
                    ['label' => 'Chuẩn', 'value' => 'NVMe PCIe Gen4 x4, M.2 2280'],
                    ['label' => 'Tốc độ đọc', 'value' => 'Đến 7000MB/s'],
                    ['label' => 'Tốc độ ghi', 'value' => 'Đến 6000MB/s'],
                    ['label' => 'Bảo hành', 'value' => '5 năm chính hãng'],
                ],
                'sort_order' => 4,
            ],
            [
                'name' => 'RAM DDR5 32GB 5600MHz',
                'slug' => 'ram-ddr5-32gb-5600mhz',
                'sku' => 'RAM-D5-32G',
                'category' => 'linh-kien-pc',
                'price' => 2490000,
                'featured' => false,
                'short_description' => 'Kit 2x16GB DDR5 bus 5600MHz, tản nhiệt nhôm.',
                'description' => 'Kit RAM 2x16GB DDR5 bus 5600MHz cho khả năng đa nhiệm mượt mà: vừa chơi game, vừa stream, vừa mở hàng chục tab trình duyệt. Tản nhiệt nhôm giúp vận hành ổn định trong thời gian dài.',
                'technical_specifications' => [
                    ['label' => 'Dung lượng', 'value' => '32GB (2x16GB)'],
                    ['label' => 'Chuẩn', 'value' => 'DDR5'],
                    ['label' => 'Bus', 'value' => '5600MHz'],
                    ['label' => 'Độ trễ', 'value' => 'CL46'],
                    ['label' => 'Tản nhiệt', 'value' => 'Nhôm, hỗ trợ XMP 3.0'],
                ],
                'sort_order' => 5,
            ],
            [
                'name' => 'Router WiFi 6 Gigabit',
                'slug' => 'router-wifi-6-gigabit',
                'sku' => 'NET-WIFI6',
                'category' => 'thiet-bi-dien-tu',
                'price' => 1290000,
                'featured' => false,
                'short_description' => 'WiFi 6 AX1800, 4 ăng-ten, phủ sóng toàn nhà.',
                'description' => 'Router WiFi 6 chuẩn AX1800 với 4 ăng-ten hiệu suất cao, phủ sóng ổn định toàn bộ ngôi nhà. Cổng LAN/WAN Gigabit tận dụng tối đa đường truyền cáp quang, công nghệ OFDMA và MU-MIMO phục vụ mượt mà hàng chục thiết bị cùng lúc.',
                'technical_specifications' => [
                    ['label' => 'Chuẩn WiFi', 'value' => 'WiFi 6 (802.11ax), AX1800'],
                    ['label' => 'Ăng-ten', 'value' => '4 ăng-ten ngoài'],
                    ['label' => 'Cổng', 'value' => '1 WAN + 3 LAN Gigabit'],
                    ['label' => 'Công nghệ', 'value' => 'OFDMA, MU-MIMO, Beamforming'],
                    ['label' => 'Quản lý', 'value' => 'App điện thoại, kiểm soát trẻ em'],
                ],
                'sort_order' => 6,
            ],
            [
                'name' => 'Màn hình 27 inch 2K 165Hz',
                'slug' => 'man-hinh-27-inch-2k-165hz',
                'sku' => 'MON-27-2K',
                'category' => 'thiet-bi-dien-tu',
                'price' => 4990000,
                'featured' => false,
                'short_description' => 'Tấm nền IPS 2K QHD, tần số quét 165Hz, 1ms.',
                'description' => 'Màn hình 27 inch độ phân giải 2K QHD trên tấm nền IPS cho màu sắc chính xác, góc nhìn rộng. Tần số quét 165Hz và thời gian phản hồi 1ms mang lại trải nghiệm gaming mượt mà, đồng thời là lựa chọn tốt cho công việc đồ họa.',
                'technical_specifications' => [
                    ['label' => 'Kích thước', 'value' => '27 inch'],
                    ['label' => 'Độ phân giải', 'value' => '2K QHD (2560×1440)'],
                    ['label' => 'Tấm nền', 'value' => 'IPS'],
                    ['label' => 'Tần số quét', 'value' => '165Hz, 1ms (MPRT)'],
                    ['label' => 'Cổng', 'value' => 'HDMI, DisplayPort'],
                ],
                'sort_order' => 7,
            ],
            [
                'name' => 'Nguồn PC 650W 80 Plus',
                'slug' => 'nguon-pc-650w-80-plus',
                'sku' => 'PSU-650W',
                'category' => 'linh-kien-pc',
                'price' => 1290000,
                'featured' => false,
                'short_description' => 'Chuẩn 80 Plus Bronze, quạt 120mm êm ái.',
                'description' => 'Nguồn máy tính công suất thực 650W đạt chuẩn 80 Plus Bronze, hiệu suất cao và tiết kiệm điện. Quạt 120mm vận hành êm ái, đầy đủ mạch bảo vệ quá áp, quá tải giúp hệ thống chạy ổn định lâu dài.',
                'technical_specifications' => [
                    ['label' => 'Công suất', 'value' => '650W thực'],
                    ['label' => 'Chuẩn', 'value' => '80 Plus Bronze'],
                    ['label' => 'Quạt', 'value' => '120mm, êm ái'],
                    ['label' => 'Bảo vệ', 'value' => 'OVP, OCP, SCP, OTP'],
                    ['label' => 'Cáp', 'value' => 'Bọc lưới, hỗ trợ VGA 8-pin'],
                ],
                'sort_order' => 8,
            ],
        ];

        foreach ($products as $item) {
            $categoryId = $categories[$item['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            Product::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $categoryId,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'technical_specifications' => $item['technical_specifications'],
                    'price' => $item['price'],
                    'status' => 'published',
                    'featured' => $item['featured'],
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}

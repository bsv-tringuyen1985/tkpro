# Cập nhật sau rà soát — 08/10/2026

Đã xử lý 14 phát hiện trong `CODE_REVIEW.md` và các lỗi bổ sung phát hiện qua kiểm thử tích hợp. Đây là thay đổi source; chưa triển khai lên máy chủ production.

## Thay đổi

| Nhóm | Kết quả |
|---|---|
| Dữ liệu frontend | Đọc đúng product/post/category trong response API; giữ dữ liệu liên quan; menu trang tĩnh dùng URL API trả về. |
| Phân quyền | API và CMS chặn tự nâng quyền, cấp quyền cao hơn quyền hiện có và sửa/xóa tài khoản có quyền cao hơn. Dùng `roles.*` cho quản lý vai trò. |
| Giao dịch user | Kiểm tra trước khi ghi; transaction bảo đảm yêu cầu bị từ chối không cập nhật một phần. Bảo vệ Super Admin cuối cùng và chính mình. |
| Mật khẩu | Admin API chặn user bị buộc đổi mật khẩu. Thêm `PUT /api/v1/auth/password`; đổi/reset mật khẩu qua API/CMS thu hồi bearer token và đổi remember token. |
| Form CMS | Lưu được vai trò lõi khi không gửi mã name; giữ nguyên mã hệ thống. Placeholder Facebook/YouTube `#` được chuẩn hóa. |
| SEO | API trả cả metadata cơ bản và khối SEO nâng cao; frontend đọc OG/canonical/robots. Migration chuyển dữ liệu SEO cũ sang cột dùng chung với CMS mà không ghi đè nội dung CMS đã sửa. Cho phép xóa giá trị SEO bằng null. |
| Schema | Chỉ API sở hữu migrations/seeds. Bỏ các migration CMS trùng bảng và sửa hướng dẫn cài đặt. |
| Upload | API/CMS cùng dùng volume `api_storage`; CMS nhập file từ volume cũ `cms_storage` ở chế độ đọc. Chỉ ghi marker sau khi nhập thành công; không khôi phục lại file đã xóa trong các lần khởi động sau. |
| Khởi động | Tách truy vấn PDO ra helper PHP để tránh lỗi escape shell; kiểm tra DB/schema có giới hạn thời gian; không che lỗi đếm users. |
| Domain/Docker | Biến domain/session truyền từ root `.env`; APP_URL là origin. URL HTTPS dựa trên cấu hình APP_URL. Có HTTP_BIND/HTTP_PORT cho reverse proxy ngoài; host và container không dùng trùng port 80. Thêm `.dockerignore`, cài PHP dependencies lúc build; Node build/run cùng nền Debian. |
| Dependencies | Next 16.4.0, React 19.3.0, Tailwind 4.3.3, Laravel 12.69.3; cập nhật lockfile. Composer resolver khóa nền PHP 8.2 để tương thích image. Thay chuỗi lint phụ thuộc fast-glob/braces bằng ESLint + TypeScript + React Hooks. |
| Kiểm thử | Bổ sung Mockery/PHPUnit, test cho CMS và frontend. Test logout tạo request auth mới và kiểm tra token thật đã bị xóa. |

Lỗi bổ sung đã sửa: `is_read` của tin liên hệ mới trả null; thông số sản phẩm API dạng danh sách gây lỗi React và lỗi form CMS; validation CMS cho phép bỏ danh mục bắt buộc hoặc nhập mô tả ngắn vượt độ dài cột. Frontend và CMS hiện đọc được cả thông số dạng danh sách mới và dictionary cũ; CMS lưu theo dạng label/value của API.

## Cập nhật bản đang chạy

1. Backup database và cả hai volume ảnh trước khi cập nhật. Giữ nguyên `APP_KEY` của hệ thống đang chạy.
2. Điền root `.env`: `SITE_URL`, `API_APP_URL`, `CMS_APP_URL`, `NEXT_PUBLIC_API_URL`, `SANCTUM_STATEFUL_DOMAINS`; để `SESSION_DOMAIN` trống và bật `SESSION_SECURE_COOKIE=true` khi dùng HTTPS. Không thêm `/admin` hoặc `/api` vào APP_URL.
3. Chạy `docker compose up -d --build`. API chạy migration bổ sung; CMS chờ schema và nhập ảnh cũ vào volume chung. Không dùng `down -v` để cập nhật.
4. Nếu nhập ảnh báo trùng đường dẫn nhưng khác nội dung, hai bản gốc được giữ nguyên và CMS dừng khởi động. Đối chiếu file và URL tham chiếu rồi xử lý xung đột trước khi khởi động lại. Không xóa volume cũ cho tới khi đã xác minh ảnh.
5. Chạy local: `composer install` trong API và CMS, `npm ci` trong frontend; chỉ chạy `php artisan migrate` từ API. Đặt `PUBLIC_STORAGE_PATH` của CMS thành đường dẫn tuyệt đối tới `api/storage/app/public`, sau đó `php artisan storage:link --force`. `.env` Laravel chỉ dùng cho chạy local; Compose đọc root `.env`.

Nếu database từng được tạo riêng từ các migration CMS cũ, cần đối chiếu schema/backup trước khi chuyển sang schema API; quy trình trên áp dụng database do API quản lý.

## Xác minh

- API: **38 tests / 156 assertions** qua, SQLite trong bộ nhớ.
- CMS: **8 tests / 40 assertions** qua, dựng schema từ migrations API trên SQLite trong bộ nhớ.
- Frontend: lint, TypeScript và **6 regression tests** qua, gồm render bảng thông số bằng React.
- **3 tests** chuyển media qua, bao gồm file trùng, xung đột nội dung và không nhập lại file đã xóa.
- Build production Next.js thành công; **13 kiểm tra HTTP** qua với API thật và database SQLite seed riêng, gồm trang chủ, nội dung, danh mục, chi tiết sản phẩm/bài viết, thông số sản phẩm, sitemap/robots và trang không tồn tại. Script: `node scripts/smoke-storefront.cjs` (API_BASE/WEB_BASE có thể cấu hình).
- Composer audit API/CMS và npm audit: **0 advisory/vulnerability** tại thời điểm kiểm tra; kết quả JSON lưu cùng thư mục này.
- Kiểm tra cú pháp PHP, biên dịch Blade, `bash -n` cho entrypoint và `docker compose --env-file .env.example config --quiet` qua.
- Chưa chạy build/full stack Docker + MySQL hoặc kiểm thử domain HTTPS thực tế vì Docker Desktop daemon chưa hoạt động. Không thay đổi database thật.

Next có thể trả HTTP 200 cho trang không tồn tại khi loading UI đã bắt đầu stream; smoke test kiểm tra nội dung not-found và robots noindex theo [hành vi được Next mô tả](https://nextjs.org/docs/app/api-reference/file-conventions/not-found).

Hướng dẫn nâng phiên bản đã đối chiếu: [Next 16](https://nextjs.org/docs/app/guides/upgrading/version-16), [Tailwind 4](https://tailwindcss.com/docs/upgrade-guide), [Laravel 12](https://laravel.com/docs/12.x/upgrade).

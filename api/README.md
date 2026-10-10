# TKPRO API — Laravel 12 REST API

API cho website TKPRO (thiết bị điện tử / camera / linh kiện PC).
Base URL: `/api/v1` — dev: `http://localhost:8000/api/v1`, qua nginx: `http://localhost/api/v1`.

## Yêu cầu

- PHP ^8.2, Composer, MySQL 8
- Dùng **chung database `tkpro`** với CMS (migrations do API sở hữu)

## Cài đặt

```bash
cd ~/workspace/tkpro/api
composer install
cp .env.example .env
php artisan key:generate
# Sửa .env: DB_HOST, DB_DATABASE=tkpro, DB_USERNAME, DB_PASSWORD, FRONTEND_URL
php artisan migrate
php artisan db:seed
php artisan storage:link   # bắt buộc để /storage/* phục vụ file upload
php artisan serve --port=8000
```

## ⚠️ Bảo mật — đổi mật khẩu seed ngay

Tài khoản seed mặc định:

- Email: `admin@example.com`
- Mật khẩu: `Admin123!`
- Vai trò: Super Admin, cờ `must_change_password = true`

**Đổi mật khẩu ngay sau lần đăng nhập đầu tiên** (CMS và admin API đều chặn thao tác cho tới khi đổi mật khẩu).
Không dùng tài khoản này ở môi trường production.

## Endpoint chính

Public: `GET /products`, `/products/{slug}`, `/categories`, `/posts`, `/post-categories`,
`/pages/{slug}`, `/banners`, `/menus/{location}`, `/settings`, `/search`,
`POST /contact-messages` (rate limit 10/phút).

Auth (Sanctum bearer): `POST /auth/login` (rate limit 5/phút), `POST /auth/logout`, `GET /auth/me`, `PUT /auth/password` (mật khẩu hiện tại + mật khẩu mới xác nhận; thu hồi mọi token).

Admin (`auth:sanctum` + `permission:{module}.{action}`): CRUD
`products`, `product-categories`, `posts`, `post-categories`, `pages`, `banners`,
`menus` (+ `menus/{id}/items`, `menu-items/{id}`), `media` (+ `media/upload`),
`settings` (PUT bulk), `users`, `roles` (+ `roles/permissions`),
`contact-messages` (list / đánh dấu đã đọc / xóa).

Web route của API: `GET /sitemap.xml`, `GET /robots.txt`.

Chi tiết đầy đủ: `~/workspace/tkpro/docs/openapi.yaml`.

## Upload media

`POST /api/v1/admin/media/upload` (multipart `file` + `alt`):
chỉ `jpg/jpeg/png/webp/svg`, ≤ 5MB (`MEDIA_MAX_SIZE_KB`),
kiểm tra kích thước bằng `getimagesize` (tối đa `MEDIA_MAX_DIMENSION` px mỗi chiều, trừ SVG),
tên file ngẫu nhiên, lưu `storage/app/public/media/YYYY/MM/`, URL public `/storage/...`.

## Phân quyền

Vai trò: `super-admin` (tất cả), `admin` (tất cả trừ quản lý vai trò), `editor`
(chỉ nội dung: xem/tạo/sửa/xuất bản, không xóa, không settings/users/roles).
Permission dạng `{module}.{action}` với action `view|create|update|delete|publish`.
Kiểm tra ở route qua middleware `permission:...`; Policies (`app/Policies`) ánh xạ tương ứng,
Super Admin được `Gate::before` cho qua tất cả.

## Test

```bash
php artisan test   # SQLite in-memory, không cần MySQL
```

## Ghi chú

- Envelope: `{success, message, data, meta?}` / lỗi `{success: false, message, errors?}`.
- Slug tiếng Việt không dấu, tự sinh từ name/title, duy nhất (thêm `-2` khi trùng).
- `price = null` nghĩa là "Liên hệ"; `price_display` trả chuỗi hiển thị sẵn.
- CORS mở cho `FRONTEND_URL`. Secure headers gắn cho mọi response.
- TUYỆT ĐỐI không có cart / checkout / order / payment trong API này.

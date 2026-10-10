# TKPRO CMS — Trang quản trị (`/admin`)

Laravel 12 + Blade + Tailwind (CDN). Session auth, **không** dùng API token.
Query MySQL trực tiếp qua Eloquent — dùng **chung** database `tkpro` với API.

## Chạy local

```bash
cd ~/workspace/tkpro/cms
composer install
cp .env.example .env
# APP_KEY dùng chung với API
# Khởi tạo DB từ thư mục api: php artisan migrate && php artisan db:seed
# PUBLIC_STORAGE_PATH=đường dẫn tuyệt đối tới api/storage/app/public
php artisan storage:link
php artisan serve --port=8001
# Mở http://localhost:8001/admin
```

Tài khoản seed (do API seed): `admin@example.com` / `Admin123!`
— đăng nhập lần đầu sẽ bị **ép đổi mật khẩu** (`users.must_change_password = true`).

## Cấu trúc

- `routes/web.php` — toàn bộ route dưới prefix `/admin`, tên route `admin.*`
- `app/Http/Controllers/` — 15 controller (Auth, Dashboard, Product, ProductCategory,
  Post, PostCategory, Page, Banner, Menu, MenuItem, Media, Setting, User, Role, ContactMessage)
- `app/Http/Middleware/` — `CheckPermission` (quyền theo route), `MustChangePassword` (ép đổi MK)
- `app/Models/` — 16 model Eloquent
- `app/Support/` — `SlugGenerator` (slug tiếng Việt), `MediaService` (upload disk public), `helpers.php`
- `database/migrations/` — không chứa migration PHP; API là chủ sở hữu schema
- `resources/views/` — Blade tiếng Việt: layouts admin/auth, dashboard, CRUD đầy đủ

## Quy ước

- Slug tiếng Việt không dấu, unique (`-2`, `-3`… khi trùng), cho phép nhập tay
- Upload ảnh: jpg/jpeg/png/webp/svg ≤ 5MB → `storage/app/public`, DB bảng `media`, URL `/storage/<path>`
- Quyền: `{module}.{view,create,update,delete,publish}`, super-admin bypass mọi kiểm tra
- Không có giỏ hàng / thanh toán / order trong CMS

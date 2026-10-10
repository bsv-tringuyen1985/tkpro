# TKPRO Full-stack — Integration Contracts

Tài liệu contract duy nhất mà cả 4 phần (frontend, api, cms, docker) PHẢI tuân thủ.
Target root: `~/workspace/tkpro/`

## 1. Kiến trúc & ports

```
Browser → nginx:80 ─┬─ /            → frontend:3000 (Next.js)
                    ├─ /api/*       → api:8000      (Laravel API, giữ nguyên path /api/...)
                    ├─ /admin/*     → cms:8001      (Laravel CMS, path bắt đầu /admin)
                    └─ /storage/*   → api:8000/storage/* (file tĩnh upload)
```

Ports (dev & compose thống nhất): frontend **3000**, api **8000**, cms **8001**, mysql **3306**, nginx **80**.

## 2. MySQL

- Database: `tkpro`, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Host: `mysql` trong docker-compose; `127.0.0.1` khi chạy local.
- **API và CMS dùng chung database. Chỉ API chạy migrations/seeds.**
  CMS không chứa migration tạo bảng và chờ API khởi tạo schema khi chạy Docker.

### 2.1 Danh sách bảng (bắt buộc đủ)

| Bảng | Cột chính |
|---|---|
| `users` | id, name, email unique, password (hash), must_change_password bool default false, remember_token, timestamps |
| `roles` | id, name unique (super-admin, admin, editor), display_name, timestamps |
| `permissions` | id, name unique (vd `products.view`), display_name, `group` (vd products), timestamps |
| `role_user` | role_id FK→roles cascade, user_id FK→users cascade, PK(role_id,user_id) |
| `permission_role` | permission_id FK→permissions cascade, role_id FK→roles cascade, PK(permission_id,role_id) |
| `product_categories` | id, name, slug unique, description nullable, image nullable, parent_id nullable FK→product_categories cascade, status enum('draft','published') default 'published', sort_order int default 0, meta_title/meta_description nullable, timestamps. Index: slug, status, parent_id |
| `products` | id, category_id FK→product_categories restrict, name, slug unique, sku unique nullable, short_description nullable, description nullable (longtext), technical_specifications nullable (json/text), price bigint nullable (VNĐ, null = "Liên hệ"), price_label nullable, thumbnail nullable, status enum('draft','published') default 'draft', featured bool default false, sort_order int default 0, meta_title/meta_description/meta_keywords nullable, timestamps. Index: slug, status, category_id, featured, created_at |
| `product_images` | id, product_id FK→products cascade, path, url, filename, mime_type, size int, alt nullable, sort_order int default 0, timestamps. Index: product_id |
| `post_categories` | id, name, slug unique, description nullable, status default 'published', sort_order default 0, timestamps. Index: slug, status |
| `posts` | id, category_id nullable FK→post_categories nullOnDelete, user_id FK→users (author), title, slug unique, excerpt nullable, content longtext nullable, featured_image nullable, status enum('draft','published') default 'draft', published_at nullable, meta_title/meta_description/meta_keywords nullable, timestamps. Index: slug, status, published_at, category_id |
| `pages` | id, title, slug unique, excerpt nullable, content longtext nullable, featured_image nullable, status default 'published', sort_order default 0, meta_title/meta_description nullable, timestamps. Index: slug, status |
| `menus` | id, name, slug unique, location (header/footer) , timestamps. Index: location |
| `menu_items` | id, menu_id FK→menus cascade, parent_id nullable FK→menu_items cascade, title, type enum('url','page') default 'url', url nullable, page_id nullable FK→pages nullOnDelete, target ('_self'), sort_order default 0, is_active bool default true, timestamps. Index: menu_id, parent_id |
| `banners` | id, title, subtitle nullable, image_desktop nullable, image_mobile nullable, link nullable, button_text nullable, sort_order default 0, is_active bool default true, timestamps. Index: is_active, sort_order |
| `media` | id, filename, path, url, mime_type, size int, width int nullable, height int nullable, alt nullable, disk default 'public', created_by nullable FK→users nullOnDelete, timestamps. Index: mime_type, created_at |
| `settings` | id, `key` unique, value text nullable, `group` default 'general', timestamps. Index: key, group |
| `contact_messages` | id, name, phone, email nullable, subject nullable, message text, is_read bool default false, read_at nullable, timestamps. Index: is_read, created_at |
| `seo_meta` | id, metable_type, metable_id (morphs), meta_title/meta_description/meta_keywords/og_title/og_description/og_image/canonical_url/robots nullable, timestamps. Index: (metable_type, metable_id) |

### 2.2 Seed data bắt buộc (khớp reference `/tmp/tkpro-ref/tkpro-website`)

- Roles: Super Admin (`super-admin`), Admin (`admin`), Editor (`editor`) + permissions
  `{products,product_categories,posts,post_categories,pages,banners,menus,media,settings,users,contact_messages} × {view,create,update,delete,publish}` gán đủ cho super-admin.
- User: `admin@example.com` / `Admin123!`, role super-admin, `must_change_password = true`.
- Product categories: `camera-giam-sat` (Camera giám sát), `linh-kien-pc` (Linh kiện PC), `thiet-bi-dien-tu` (Thiết bị điện tử).
- Post categories: `khuyen-mai` (Khuyến mãi), `pc-build` (PC Build), `huong-dan` (Hướng dẫn).
- Products (status published, featured=true cho 4 sản phẩm đầu):
  1. Camera WiFi 2K trong nhà | camera-giam-sat | 890000 | "Camera WiFi độ phân giải 2K, đàm thoại 2 chiều, quay đêm có màu." | SKU CAM-WIFI-2K
  2. Camera IP AI ngoài trời 4MP | camera-giam-sat | 1690000 | "Chống nước IP66, nhận diện người bằng AI, cảnh báo chuyển động." | SKU CAM-IP-4MP
  3. VGA RTX Gaming 4060 8GB | linh-kien-pc | 7990000 | "Card đồ họa RTX 4060 8GB GDDR6, chiến mượt game AAA ở Full HD/2K." | SKU VGA-4060-8G
  4. SSD NVMe 1TB Gen4 | linh-kien-pc | 1590000 | "Tốc độ đọc đến 7000MB/s, bảo hành 5 năm chính hãng." | SKU SSD-1TB-G4
  5. RAM DDR5 32GB 5600MHz | linh-kien-pc | 2490000 | "Kit 2x16GB DDR5 bus 5600MHz, tản nhiệt nhôm." | SKU RAM-D5-32G
  6. Router WiFi 6 Gigabit | thiet-bi-dien-tu | 1290000 | "WiFi 6 AX1800, 4 ăng-ten, phủ sóng toàn nhà." | SKU NET-WIFI6
  7. Màn hình 27 inch 2K 165Hz | thiet-bi-dien-tu | 4990000 | "Tấm nền IPS 2K QHD, tần số quét 165Hz, 1ms." | SKU MON-27-2K
  8. Nguồn PC 650W 80 Plus | linh-kien-pc | 1290000 | "Chuẩn 80 Plus Bronze, quạt 120mm êm ái." | SKU PSU-650W
- Posts (published):
  1. [khuyen-mai] "Combo camera gia đình — bảo vệ ngôi nhà 24/7" — excerpt "Gợi ý lựa chọn camera phù hợp cho căn hộ, nhà phố và cửa hàng." — content: "Bài viết chi tiết về cách chọn combo camera cho gia đình: số lượng mắt camera, vị trí lắp đặt, dung lượng lưu trữ và những lưu ý khi thi công để hệ thống hoạt động ổn định 24/7."
  2. [pc-build] "Chọn cấu hình PC đúng nhu cầu và ngân sách" — excerpt "Từ văn phòng, học tập đến gaming và đồ họa chuyên nghiệp." — content: "Hướng dẫn chọn cấu hình PC theo từng nhu cầu: văn phòng, học tập, gaming và đồ họa. Cách cân đối giữa CPU, VGA, RAM và SSD để có hiệu năng tốt nhất trong tầm giá."
  3. [huong-dan] "SSD NVMe, RAM và nguồn: nâng cấp gì trước?" — excerpt "Những nâng cấp mang lại hiệu quả rõ rệt cho máy tính." — content: "Nếu máy tính chạy chậm, đâu là linh kiện nên nâng cấp trước? Bài viết phân tích hiệu quả của từng nâng cấp: SSD giúp khởi động nhanh, RAM giúp đa nhiệm mượt, nguồn đảm bảo ổn định lâu dài."
- Pages: `gioi-thieu` (Giới thiệu), `dich-vu` (Dịch vụ), `lien-he` (Liên hệ) + `trang-chu` meta — nội dung tiếng Việt hợp lý về TKPRO.
- Menus: `header` (Trang chủ `/`, Sản phẩm `/san-pham`, Camera `/san-pham/camera-giam-sat`, Linh kiện PC `/san-pham/linh-kien-pc`, Tin tức `/tin-tuc`, Liên hệ `/lien-he`); `footer` (Sản phẩm, Dịch vụ, TKPRO theo 3 cột như reference).
- Banners: 1 hero banner — title "Nâng cấp không gian, làm chủ công nghệ.", subtitle "Khám phá hệ sinh thái thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Tư vấn đúng nhu cầu, lắp đặt chuẩn và bảo hành rõ ràng.", button_text "Xem sản phẩm", link "/san-pham", is_active true.
- Settings (group general/contact/social/seo):
  site_name=TKPRO, hotline=1900 6868, email=support@techvision.vn,
  address="123 Nguyễn Trãi, TP.HCM", working_hours="08:00 — 21:00 mỗi ngày",
  announcement="Hàng chính hãng · Bảo hành tận tâm · Giao hàng toàn quốc",
  facebook=#, youtube=#, zalo=#, google_maps_embed="",
  copyright="© 2026 TKPRO · Điện tử · Camera · Linh kiện máy tính · Bảo hành chính hãng",
  footer_about="Thiết bị điện tử, camera và linh kiện máy tính chính hãng."

## 3. API contract (`/api/v1`)

Base URL dev: `http://localhost:8000/api/v1`. Qua nginx: `http://localhost/api/v1`.

### 3.1 Envelope

Thành công:
```json
{ "success": true, "message": "Success", "data": {}, "meta": { "current_page": 1, "total": 0 } }
```
Lỗi:
```json
{ "success": false, "message": "Validation failed", "errors": { "name": ["..."] } }
```
Status code đúng chuẩn (200/201/204/401/403/404/422/429/500).

### 3.2 Endpoints

Public (không auth):
```
GET  /api/v1/products                    (q, category slug, featured=1, per_page, sort)
GET  /api/v1/products/{slug}            (kèm category, images, related)
GET  /api/v1/categories                  (cây danh mục + đếm sản phẩm)
GET  /api/v1/categories/{slug}
GET  /api/v1/posts                       (q, category slug, per_page)
GET  /api/v1/posts/{slug}               (kèm category, author)
GET  /api/v1/post-categories
GET  /api/v1/pages/{slug}
GET  /api/v1/banners                     (chỉ active, sort)
GET  /api/v1/menus/{location}            (header|footer, cây item)
GET  /api/v1/settings                    (chỉ group public: general, contact, social, seo)
POST /api/v1/contact-messages            (name, phone required; email, subject, message; rate-limit)
GET  /api/v1/search?q=                   (products + posts gộp)
GET  /sitemap.xml   (route web của api, không nằm /api/v1)
GET  /robots.txt    (route web của api)
```
Auth (Sanctum bearer):
```
POST /api/v1/auth/login                  (email, password → token)
POST /api/v1/auth/logout                 (auth)
GET  /api/v1/auth/me                     (auth)
PUT  /api/v1/auth/password               (auth, 5/phút; current_password, password, password_confirmation)
```
Admin (auth:sanctum + policy theo permission):
```
CRUD /api/v1/admin/products, /admin/product-categories, /admin/posts,
     /admin/post-categories, /admin/pages, /admin/banners, /admin/menus,
     /admin/users, /admin/roles
POST /api/v1/admin/media/upload           (file; jpg/jpeg/png/webp/svg; ≤5MB; check dimensions)
GET  /api/v1/admin/media                  (q, mime filter, paginate)
DELETE /api/v1/admin/media/{id}
PUT  /api/v1/admin/settings               (bulk key/value)
GET  /api/v1/admin/contact-messages       (filter is_read, paginate)
PUT  /api/v1/admin/contact-messages/{id}  (đánh dấu đã đọc)
DELETE /api/v1/admin/contact-messages/{id}
```

### 3.3 Quy ước khác

- Slug: tiếng Việt không dấu (đ→d), lowercase, gạch ngang, unique (thêm `-2` khi trùng), tự sinh từ name/title, cho phép sửa tay.
- Media: file lưu `storage/app/public/...`, DB bảng `media` chỉ lưu path/url/filename/mime_type/size/width/height/alt; URL public dạng `/storage/<path>`; validate mime + size + dimensions; tên file ngẫu nhiên.
- CORS: cho phép origin của frontend. Rate limit: login 5/phút, contact 10/phút.
- Không bao giờ expose `.env`, APP_KEY, DB credentials.

## 4. Frontend contract

- `NEXT_PUBLIC_API_URL` (browser): dev `http://localhost:8000/api/v1`, docker `http://localhost/api/v1`.
- `API_INTERNAL_URL` (server components trong docker): `http://api:8000/api/v1`, fallback = NEXT_PUBLIC_API_URL.
- Mọi dữ liệu qua `lib/api.ts` + `services/*`; KHÔNG hard-code sản phẩm/bài viết/settings.
- Routes: `/`, `/gioi-thieu`, `/san-pham`, `/san-pham/[category]`, `/san-pham/[category]/[slug]`,
  `/dich-vu`, `/tin-tuc`, `/tin-tuc/[slug]`, `/lien-he`, `/tim-kiem`.
- Logo: copy `/tmp/tkpro-ref/tkpro-website/static/img/logo.svg` → `frontend/public/logo.svg`.
- Brand: xanh `#087a3b` primary, `#065d2d` đậm, đỏ `#e32121` cho nút CTA, nền `#f6faf7`,
  chữ `#10231a`. Giá hiển thị "Giá tham khảo: xxx" hoặc "Liên hệ"; CTA "Liên hệ tư vấn".
  TUYỆT ĐỐI không giỏ hàng/thanh toán/order.

## 5. CMS contract

- URL `/admin` (qua nginx) / local `http://localhost:8001/admin`.
- Session auth (không dùng token API); login/logout/forgot/reset/change password, remember me.
- Query MySQL trực tiếp qua Eloquent (KHÔNG gọi qua API).
- Tiếng Việt, Blade + Tailwind, sidebar/topbar/breadcrumb/toast/dialog/phân trang/tìm kiếm/lọc.
- Ép đổi mật khẩu khi `users.must_change_password = true` (seed admin có cờ này).

## 6. Env files (mỗi phần có `.env.example`)

- frontend: `NEXT_PUBLIC_API_URL=`, `API_INTERNAL_URL=`
- api: `APP_NAME=TKPRO API`, `APP_ENV`, `APP_KEY`, `APP_URL=http://localhost:8000`,
  `DB_CONNECTION=mysql`, `DB_HOST=`, `DB_PORT=3306`, `DB_DATABASE=tkpro`, `DB_USERNAME=`, `DB_PASSWORD=`,
  `FRONTEND_URL=http://localhost:3000`, `SANCTUM_STATEFUL_DOMAINS=`
- cms: tương tự api nhưng `APP_NAME=TKPRO CMS`, `APP_URL=http://localhost:8001`,
  `SESSION_DOMAIN=`, `ADMIN_PATH_PREFIX=admin`

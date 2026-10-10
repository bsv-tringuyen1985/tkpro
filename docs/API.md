# TKPRO — API Documentation

Tài liệu đầy đủ các endpoint của **TKPRO API** (`/api/v1`), theo contract `docs/CONTRACTS.md` mục 3.

- Base URL dev (trực tiếp): `http://localhost:8000/api/v1`
- Base URL qua nginx (browser/production): `http://localhost/api/v1`
- Định dạng: JSON. Mọi response đều dùng **envelope** thống nhất.

## 1. Envelope (định dạng response)

### 1.1 Thành công

```json
{
  "success": true,
  "message": "Success",
  "data": {},
  "meta": { "current_page": 1, "per_page": 12, "total": 0, "last_page": 1 }
}
```

- `meta` chỉ xuất hiện với response phân trang.
- `message` mô tả ngắn gọn kết quả (tiếng Anh hoặc tiếng Việt tùy implement, frontend hiển thị được).

### 1.2 Lỗi

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["Trường tên là bắt buộc."]
  }
}
```

- `errors` chỉ xuất hiện khi lỗi validation (422).

### 1.3 Status code

| Code | Ý nghĩa | Khi nào |
|------|---------|---------|
| 200 | OK | GET/PUT thành công |
| 201 | Created | POST tạo mới thành công |
| 204 | No Content | DELETE thành công (không body) |
| 401 | Unauthorized | Thiếu/sai Bearer token |
| 403 | Forbidden | Đã login nhưng thiếu permission |
| 404 | Not Found | Resource không tồn tại |
| 422 | Unprocessable | Validation thất bại |
| 429 | Too Many Requests | Vượt rate limit (login, contact) |
| 500 | Server Error | Lỗi hệ thống |

### 1.4 Ví dụ lỗi mẫu

**401 — chưa đăng nhập:**
```json
{ "success": false, "message": "Unauthenticated." }
```

**403 — thiếu quyền:**
```json
{ "success": false, "message": "Bạn không có quyền thực hiện hành động này." }
```

**404 — không tìm thấy:**
```json
{ "success": false, "message": "Không tìm thấy sản phẩm." }
```

**422 — validation:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "phone": ["Số điện thoại không đúng định dạng."],
    "message": ["Nội dung tin nhắn phải có ít nhất 10 ký tự."]
  }
}
```

**429 — rate limit:**
```json
{ "success": false, "message": "Quá nhiều yêu cầu. Vui lòng thử lại sau." }
```

---

## 2. Public endpoints (không cần auth)

### 2.1 Sản phẩm

#### `GET /api/v1/products` — Danh sách sản phẩm
Chỉ trả sản phẩm `status = published`.

| Param | Kiểu | Mô tả |
|-------|------|-------|
| `q` | string | Tìm theo tên/SKU/mô tả |
| `category` | string | Slug danh mục (vd `camera-giam-sat`) |
| `featured` | 0/1 | `featured=1` → chỉ sản phẩm nổi bật |
| `per_page` | int | Số item/trang (mặc định 12, tối đa 60) |
| `sort` | string | `latest` (mặc định), `price_asc`, `price_desc`, `name` |

Ví dụ response:
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "Camera WiFi 2K trong nhà",
      "slug": "camera-wifi-2k-trong-nha",
      "sku": "CAM-WIFI-2K",
      "short_description": "Camera WiFi độ phân giải 2K, đàm thoại 2 chiều, quay đêm có màu.",
      "price": 890000,
      "price_label": null,
      "thumbnail": "/storage/products/camera-wifi-2k.jpg",
      "featured": true,
      "category": { "id": 1, "name": "Camera giám sát", "slug": "camera-giam-sat" }
    }
  ],
  "meta": { "current_page": 1, "per_page": 12, "total": 8, "last_page": 1 }
}
```

> `price = null` nghĩa là "Liên hệ" (giá tham khảo không công khai).
> Giá hiển thị ở frontend: `"Giá tham khảo: 890.000 ₫"` hoặc `"Liên hệ"`.

#### `GET /api/v1/products/{slug}` — Chi tiết sản phẩm
Kèm `category`, `images` (danh sách ảnh), `related` (sản phẩm cùng danh mục).

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Camera WiFi 2K trong nhà",
    "slug": "camera-wifi-2k-trong-nha",
    "sku": "CAM-WIFI-2K",
    "short_description": "Camera WiFi độ phân giải 2K, đàm thoại 2 chiều, quay đêm có màu.",
    "description": "<p>Nội dung chi tiết...</p>",
    "technical_specifications": { "Độ phân giải": "2K", "Kết nối": "WiFi" },
    "price": 890000,
    "price_label": null,
    "thumbnail": "/storage/products/camera-wifi-2k.jpg",
    "featured": true,
    "category": { "id": 1, "name": "Camera giám sát", "slug": "camera-giam-sat" },
    "images": [
      { "id": 1, "url": "/storage/products/camera-wifi-2k-1.jpg", "alt": "Mặt trước" }
    ],
    "related": [ { "id": 2, "name": "Camera IP AI ngoài trời 4MP", "slug": "camera-ip-ai-ngoai-troi-4mp", "price": 1690000, "thumbnail": "/storage/products/camera-ip-4mp.jpg" } ],
    "meta": { "meta_title": "Camera WiFi 2K trong nhà | TKPRO", "meta_description": "...", "canonical_url": "https://tkpro.vn/san-pham/camera-giam-sat/camera-wifi-2k-trong-nha" }
  }
}
```

### 2.2 Danh mục sản phẩm

#### `GET /api/v1/categories` — Cây danh mục + đếm sản phẩm
Trả cây danh mục (cha → con qua `children`), mỗi node có `products_count` (chỉ đếm published).

```json
{
  "success": true,
  "data": [
    {
      "id": 1, "name": "Camera giám sát", "slug": "camera-giam-sat",
      "description": null, "image": null, "products_count": 2,
      "children": []
    }
  ]
}
```

#### `GET /api/v1/categories/{slug}` — Chi tiết một danh mục
```json
{
  "success": true,
  "data": {
    "id": 1, "name": "Camera giám sát", "slug": "camera-giam-sat",
    "description": null, "image": null, "products_count": 2, "children": []
  }
}
```

### 2.3 Bài viết

#### `GET /api/v1/posts` — Danh sách bài viết
Chỉ trả bài `status = published`, sắp xếp `published_at` mới nhất.

| Param | Kiểu | Mô tả |
|-------|------|-------|
| `q` | string | Tìm theo tiêu đề/nội dung |
| `category` | string | Slug danh mục bài viết (vd `khuyen-mai`) |
| `per_page` | int | Mặc định 10, tối đa 60 |

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Combo camera gia đình — bảo vệ ngôi nhà 24/7",
      "slug": "combo-camera-gia-dinh-bao-ve-ngoi-nha-24-7",
      "excerpt": "Gợi ý lựa chọn camera phù hợp cho căn hộ, nhà phố và cửa hàng.",
      "featured_image": "/storage/posts/combo-camera.jpg",
      "published_at": "2026-10-01T08:00:00+07:00",
      "category": { "id": 1, "name": "Khuyến mãi", "slug": "khuyen-mai" }
    }
  ],
  "meta": { "current_page": 1, "per_page": 10, "total": 3, "last_page": 1 }
}
```

#### `GET /api/v1/posts/{slug}` — Chi tiết bài viết
Kèm `category` và `author` (name).

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Combo camera gia đình — bảo vệ ngôi nhà 24/7",
    "slug": "combo-camera-gia-dinh-bao-ve-ngoi-nha-24-7",
    "excerpt": "Gợi ý lựa chọn camera phù hợp cho căn hộ, nhà phố và cửa hàng.",
    "content": "<p>Bài viết chi tiết...</p>",
    "featured_image": "/storage/posts/combo-camera.jpg",
    "published_at": "2026-10-01T08:00:00+07:00",
    "category": { "id": 1, "name": "Khuyến mãi", "slug": "khuyen-mai" },
    "author": { "id": 1, "name": "Admin" }
  }
}
```

#### `GET /api/v1/post-categories` — Danh sách danh mục bài viết
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Khuyến mãi", "slug": "khuyen-mai", "posts_count": 1 },
    { "id": 2, "name": "PC Build", "slug": "pc-build", "posts_count": 1 },
    { "id": 3, "name": "Hướng dẫn", "slug": "huong-dan", "posts_count": 1 }
  ]
}
```

### 2.4 Trang tĩnh

#### `GET /api/v1/pages/{slug}` — Nội dung trang (giới thiệu, dịch vụ, liên hệ...)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Giới thiệu",
    "slug": "gioi-thieu",
    "excerpt": null,
    "content": "<p>TKPRO là...</p>",
    "featured_image": null
  }
}
```

### 2.5 Banner

#### `GET /api/v1/banners` — Banner đang active, sắp xếp theo `sort_order`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Nâng cấp không gian, làm chủ công nghệ.",
      "subtitle": "Khám phá hệ sinh thái thiết bị điện tử...",
      "image_desktop": "/storage/banners/hero-desktop.jpg",
      "image_mobile": "/storage/banners/hero-mobile.jpg",
      "link": "/san-pham",
      "button_text": "Xem sản phẩm"
    }
  ]
}
```

### 2.6 Menu

#### `GET /api/v1/menus/{location}` — Menu theo vị trí (`header` | `footer`)
Trả cây item (cha → con), chỉ item `is_active`.

```json
{
  "success": true,
  "data": {
    "id": 1, "name": "Menu chính", "slug": "header", "location": "header",
    "items": [
      { "id": 1, "title": "Trang chủ", "url": "/", "target": "_self", "children": [] },
      {
        "id": 2, "title": "Sản phẩm", "url": "/san-pham", "target": "_self",
        "children": [
          { "id": 3, "title": "Camera", "url": "/san-pham/camera-giam-sat", "target": "_self", "children": [] }
        ]
      }
    ]
  }
}
```

### 2.7 Settings

#### `GET /api/v1/settings` — Cài đặt public
Chỉ trả các group public: `general`, `contact`, `social`, `seo` (dạng key → value).

```json
{
  "success": true,
  "data": {
    "site_name": "TKPRO",
    "hotline": "1900 6868",
    "email": "support@techvision.vn",
    "address": "123 Nguyễn Trãi, TP.HCM",
    "working_hours": "08:00 — 21:00 mỗi ngày",
    "announcement": "Hàng chính hãng · Bảo hành tận tâm · Giao hàng toàn quốc",
    "facebook": "#",
    "youtube": "#",
    "zalo": "#",
    "copyright": "© 2026 TKPRO · Điện tử · Camera · Linh kiện máy tính · Bảo hành chính hãng"
  }
}
```

### 2.8 Liên hệ

#### `POST /api/v1/contact-messages` — Gửi tin nhắn liên hệ
Rate limit: **10 request/phút/IP**.

Body (JSON):
```json
{
  "name": "Nguyễn Văn A",
  "phone": "0901234567",
  "email": "a@example.com",
  "subject": "Tư vấn camera",
  "message": "Tôi cần tư vấn lắp 4 camera cho nhà phố..."
}
```

- Bắt buộc: `name`, `phone` (định dạng số điện thoại VN), `message` (tối thiểu 10 ký tự).
- Tùy chọn: `email` (phải đúng định dạng nếu gửi), `subject`.

Response `201`:
```json
{
  "success": true,
  "message": "Cảm ơn bạn đã liên hệ! TKPRO sẽ phản hồi trong thời gian sớm nhất.",
  "data": { "id": 1 }
}
```

### 2.9 Tìm kiếm

#### `GET /api/v1/search?q=` — Tìm kiếm gộp sản phẩm + bài viết
| Param | Kiểu | Mô tả |
|-------|------|-------|
| `q` | string | Từ khóa (bắt buộc, tối thiểu 2 ký tự) |

```json
{
  "success": true,
  "data": {
    "products": [ { "id": 1, "name": "Camera WiFi 2K trong nhà", "slug": "camera-wifi-2k-trong-nha", "price": 890000, "thumbnail": "/storage/products/camera-wifi-2k.jpg" } ],
    "posts": [ { "id": 1, "title": "Combo camera gia đình — bảo vệ ngôi nhà 24/7", "slug": "combo-camera-gia-dinh-bao-ve-ngoi-nha-24-7" } ]
  }
}
```

### 2.10 Sitemap & robots (route web của api, KHÔNG nằm /api/v1)

| Method | Path | Mô tả |
|--------|------|-------|
| `GET` | `/sitemap.xml` | Sitemap XML (sản phẩm, bài viết, trang tĩnh) |
| `GET` | `/robots.txt` | Quy tắc cho crawler |

> Qua nginx: `http://localhost/sitemap.xml` và `http://localhost/robots.txt`.

---

## 3. Auth (Sanctum Bearer token)

Header cho mọi request cần auth:
```
Authorization: Bearer <token>
Accept: application/json
```

#### `POST /api/v1/auth/login` — Đăng nhập
Rate limit: **5 request/phút/IP**.

Body:
```json
{ "email": "admin@example.com", "password": "Admin123!" }
```

Response `200`:
```json
{
  "success": true,
  "message": "Đăng nhập thành công.",
  "data": {
    "token": "1|AbCdEfGh...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "roles": ["super-admin"],
      "permissions": ["products.view", "products.create", "..."],
      "must_change_password": true
    }
  }
}
```

> Nếu `must_change_password = true`, client/CMS phải ép người dùng đổi mật khẩu ngay sau lần đăng nhập đầu tiên.

Lỗi `401`:
```json
{ "success": false, "message": "Email hoặc mật khẩu không đúng." }
```

#### `POST /api/v1/auth/logout` — Đăng xuất (auth)
Thu hồi token hiện tại. Response `200`:
```json
{ "success": true, "message": "Đăng xuất thành công." }
```

#### `GET /api/v1/auth/me` — Thông tin user hiện tại (auth)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "roles": ["super-admin"],
    "permissions": ["products.view", "products.create", "..."]
  }
}
```

---

#### `PUT /api/v1/auth/password` — Đổi mật khẩu (auth, 5 lần/phút)

Body: `current_password`, `password` (8–100 ký tự, khác mật khẩu cũ), `password_confirmation`.
Thành công trả `200`, xóa cờ `must_change_password` và thu hồi mọi bearer token của tài khoản.
Client phải đăng nhập lại để lấy token mới. Sai mật khẩu hiện tại trả `422`.

## 4. Admin endpoints (auth:sanctum + permission)

Mọi endpoint dưới `/api/v1/admin/*` yêu cầu Bearer token **và** permission tương ứng
(kiểm tra theo policy, vd `products.view` cho GET list, `products.create` cho POST...).

Tài khoản có `must_change_password=true` nhận `403` kèm `must_change_password: true`.
Các endpoint `/auth/me`, `/auth/password`, `/auth/logout` vẫn dùng được.
Người quản lý chỉ được gán vai trò/quyền mà chính mình có và không được sửa/xóa người có quyền cao hơn.

### 4.1 CRUD resources

| Resource | Path base | Permissions |
|----------|-----------|-------------|
| Sản phẩm | `/api/v1/admin/products` | `products.*` |
| Danh mục sản phẩm | `/api/v1/admin/product-categories` | `product_categories.*` |
| Bài viết | `/api/v1/admin/posts` | `posts.*` |
| Danh mục bài viết | `/api/v1/admin/post-categories` | `post_categories.*` |
| Trang tĩnh | `/api/v1/admin/pages` | `pages.*` |
| Banner | `/api/v1/admin/banners` | `banners.*` |
| Menu | `/api/v1/admin/menus` | `menus.*` |
| Người dùng | `/api/v1/admin/users` | `users.*` |
| Vai trò | `/api/v1/admin/roles` | `roles.*` |

Quy ước CRUD (ví dụ với products):

| Method | Path | Quyền | Mô tả |
|--------|------|-------|-------|
| `GET` | `/api/v1/admin/products` | `products.view` | Danh sách (kể cả draft), filter `q`, `status`, `category_id`, phân trang |
| `POST` | `/api/v1/admin/products` | `products.create` | Tạo mới → `201` |
| `GET` | `/api/v1/admin/products/{id}` | `products.view` | Chi tiết (kèm category, images, seo) |
| `PUT`/`PATCH` | `/api/v1/admin/products/{id}` | `products.update` | Cập nhật → `200` |
| `DELETE` | `/api/v1/admin/products/{id}` | `products.delete` | Xóa → `204` (không body) |

Ví dụ tạo sản phẩm `POST /api/v1/admin/products`:
```json
{
  "name": "Camera WiFi 2K trong nhà",
  "slug": "camera-wifi-2k-trong-nha",
  "sku": "CAM-WIFI-2K",
  "category_id": 1,
  "short_description": "Camera WiFi độ phân giải 2K...",
  "description": "<p>Chi tiết...</p>",
  "technical_specifications": { "Độ phân giải": "2K" },
  "price": 890000,
  "thumbnail": "/storage/products/camera-wifi-2k.jpg",
  "status": "published",
  "featured": true,
  "sort_order": 0,
  "meta_title": "Camera WiFi 2K trong nhà | TKPRO"
}
```

- `slug`: tự sinh từ `name` nếu bỏ trống (tiếng Việt không dấu, lowercase, gạch ngang; trùng thì thêm `-2`); cho phép sửa tay.
- `status`: `draft` | `published`. Riêng products/posts còn quyền `publish` để chuyển trạng thái xuất bản.
- Xóa danh mục sản phẩm khi còn sản phẩm → `422` (FK restrict).

Ví dụ danh sách admin `GET /api/v1/admin/products?q=camera&status=published&per_page=20`:
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Camera WiFi 2K trong nhà", "slug": "camera-wifi-2k-trong-nha", "sku": "CAM-WIFI-2K", "price": 890000, "status": "published", "featured": true, "category": { "id": 1, "name": "Camera giám sát" }, "created_at": "2026-10-07T10:00:00+07:00" }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 2, "last_page": 1 }
}
```

### 4.2 Media

#### `POST /api/v1/admin/media/upload` — Upload file (quyền `media.create`)
Form-data: `file` (1 file), `alt` (tùy chọn).

Validate:
- Mime: `jpg`, `jpeg`, `png`, `webp`, `svg`
- Dung lượng ≤ **5MB**
- Kiểm tra dimensions (ảnh phải đọc được kích thước; svg bỏ qua)
- Tên file lưu ngẫu nhiên, tránh trùng

Response `201`:
```json
{
  "success": true,
  "message": "Upload thành công.",
  "data": {
    "id": 12,
    "filename": "a1b2c3d4.jpg",
    "path": "media/2026/10/a1b2c3d4.jpg",
    "url": "/storage/media/2026/10/a1b2c3d4.jpg",
    "mime_type": "image/jpeg",
    "size": 184320,
    "width": 1200,
    "height": 800,
    "alt": "Ảnh banner"
  }
}
```

> File vật lý lưu tại `storage/app/public/...`, serve public qua `/storage/<path>`
> (nginx route `/storage/` → api). DB bảng `media` chỉ lưu path/url/filename/mime_type/size/width/height/alt.

#### `GET /api/v1/admin/media` — Danh sách media (quyền `media.view`)
| Param | Mô tả |
|-------|-------|
| `q` | Tìm theo filename/alt |
| `mime` | Lọc `image/jpeg`, `image/png`... |
| `per_page` | Mặc định 24 |

#### `DELETE /api/v1/admin/media/{id}` — Xóa media (quyền `media.delete`)
Xóa cả file vật lý. → `204`.

### 4.3 Settings

#### `PUT /api/v1/admin/settings` — Cập nhật bulk settings (quyền `settings.update`)
Body: object key → value (chỉ các key cho phép).

```json
{
  "site_name": "TKPRO",
  "hotline": "1900 6868",
  "announcement": "Khuyến mãi tháng 10..."
}
```

Response `200`:
```json
{ "success": true, "message": "Cập nhật cài đặt thành công.", "data": { "site_name": "TKPRO", "hotline": "1900 6868" } }
```

### 4.4 Contact messages

#### `GET /api/v1/admin/contact-messages` — Danh sách tin nhắn (quyền `contact_messages.view`)
| Param | Mô tả |
|-------|-------|
| `is_read` | `0` = chưa đọc, `1` = đã đọc |
| `per_page` | Mặc định 20 |

```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Nguyễn Văn A", "phone": "0901234567", "email": "a@example.com", "subject": "Tư vấn camera", "message": "Tôi cần tư vấn...", "is_read": false, "read_at": null, "created_at": "2026-10-07T14:00:00+07:00" }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 5, "last_page": 1 }
}
```

#### `PUT /api/v1/admin/contact-messages/{id}` — Đánh dấu đã đọc (quyền `contact_messages.update`)
Body: `{ "is_read": true }` → response `200` với object đã cập nhật (có `read_at`).

#### `DELETE /api/v1/admin/contact-messages/{id}` — Xóa tin nhắn (quyền `contact_messages.delete`)
→ `204`.

---

## 5. Quy ước chung

### 5.1 Slug
- Tiếng Việt không dấu (`đ` → `d`), lowercase, khoảng trắng → gạch ngang, loại bỏ ký tự đặc biệt.
- Unique: trùng thì thêm hậu tố `-2`, `-3`...
- Tự sinh từ `name`/`title` khi tạo; cho phép sửa tay ở CMS.

### 5.2 Phân trang
Mọi list hỗ trợ `page` và `per_page`; response luôn có `meta` gồm `current_page`, `per_page`, `total`, `last_page`.

### 5.3 CORS
Cho phép origin của frontend (`FRONTEND_URL`, mặc định `http://localhost:3000` local / `http://localhost` qua nginx).

### 5.4 Rate limit
| Endpoint | Giới hạn |
|----------|----------|
| `POST /api/v1/auth/login` | 5 request/phút/IP |
| `POST /api/v1/contact-messages` | 10 request/phút/IP |

### 5.5 Bảo mật
- KHÔNG bao giờ expose `.env`, `APP_KEY`, DB credentials qua API.
- Password luôn hash (bcrypt); response user không bao giờ chứa password hash.
- File `docs/openapi.yaml` (nếu worker api cung cấp) là bản machine-readable của tài liệu này.

---

## 6. Ví dụ gọi nhanh (cURL)

```bash
BASE=http://localhost/api/v1

# Public
curl "$BASE/products?featured=1&per_page=8"
curl "$BASE/products/camera-wifi-2k-trong-nha"
curl "$BASE/settings"

# Login → lấy token
TOKEN=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Admin123!"}' | php -r 'echo json_decode(stream_get_contents(STDIN))->data->token;')

# Admin: danh sách sản phẩm (kể cả draft)
curl -H "Authorization: Bearer $TOKEN" "$BASE/admin/products?per_page=20"

# Admin: upload media
curl -H "Authorization: Bearer $TOKEN" -F "file=@/tmp/anh.jpg" -F "alt=Ảnh demo" "$BASE/admin/media/upload"
```

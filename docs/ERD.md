# TKPRO — Database ERD

Mô tả toàn bộ database `tkpro` (MySQL 8, charset `utf8mb4`, collation `utf8mb4_unicode_ci`).
Database **dùng chung** cho API và CMS; chỉ **API** sở hữu và chạy migrations.

> Ký hiệu: `PK` = primary key, `FK` = foreign key, `UQ` = unique, `IDX` = index thường.

## 1. Sơ đồ quan hệ (text diagram)

```
users ──< role_user >── roles ──< permission_role >── permissions
  │                        (N—N qua 2 bảng pivot)                (N—N qua 2 bảng pivot)
  │
  ├─< posts (author: posts.user_id)
  └─< media (uploader: media.created_by)

product_categories ─┬─< product_categories (parent_id: cây danh mục)
                    └─< products ─┬─< product_images
                                  └─< seo_meta (morph: metable)

post_categories ─< posts ─┬─< seo_meta (morph: metable)
                          └─> users (author)

pages ─┬─< menu_items (page_id, khi type='page')
       └─< seo_meta (morph: metable)

menus ─< menu_items ─┬─< menu_items (parent_id: cây menu)
                     └─> pages (page_id, nullable)

banners            (độc lập)
settings           (độc lập, key-value)
contact_messages   (độc lập)
```

Hành vi xóa (on delete):
- `cascade` — xóa cha kéo theo con (vd xóa product → xóa product_images).
- `restrict` — chặn xóa cha khi còn con (vd xóa category còn product → lỗi 422).
- `nullOnDelete` / `set null` — con giữ lại, FK thành NULL (vd xóa post_category → posts.category_id = NULL).

## 2. Chi tiết từng bảng

### 2.1 `users` — Người dùng quản trị
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK, auto increment |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | UQ, NOT NULL |
| password | varchar(255) | hash bcrypt, NOT NULL |
| must_change_password | tinyint(1) | default false — ép đổi pass lần đầu |
| remember_token | varchar(100) | nullable |
| created_at / updated_at | timestamp | nullable |

### 2.2 `roles` — Vai trò
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | UQ — `super-admin`, `admin`, `editor` |
| display_name | varchar(255) | nullable — "Super Admin"... |
| created_at / updated_at | timestamp | |

### 2.3 `permissions` — Quyền hạn
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | UQ — vd `products.view` |
| display_name | varchar(255) | nullable |
| `group` | varchar(255) | nullable — vd `products` |
| created_at / updated_at | timestamp | |

> Ma trận permission: 11 nhóm (`products`, `product_categories`, `posts`, `post_categories`,
> `pages`, `banners`, `menus`, `media`, `settings`, `users`, `contact_messages`)
> × 5 hành động (`view`, `create`, `update`, `delete`, `publish`) = 55 quyền,
> gán đủ cho `super-admin` khi seed.

### 2.4 `role_user` — Pivot user ↔ role
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| role_id | bigint unsigned | FK → `roles.id` **cascade**, PK cùng user_id |
| user_id | bigint unsigned | FK → `users.id` **cascade**, PK cùng role_id |

### 2.5 `permission_role` — Pivot permission ↔ role
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| permission_id | bigint unsigned | FK → `permissions.id` **cascade**, PK cùng role_id |
| role_id | bigint unsigned | FK → `roles.id` **cascade**, PK cùng permission_id |

### 2.6 `product_categories` — Danh mục sản phẩm (cây)
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ + IDX |
| description | text | nullable |
| image | varchar(255) | nullable — path ảnh |
| parent_id | bigint unsigned | nullable, FK → `product_categories.id` **cascade** + IDX |
| status | enum('draft','published') | default 'published' + IDX |
| sort_order | int | default 0 |
| meta_title / meta_description | varchar/text | nullable (SEO fallback) |
| created_at / updated_at | timestamp | |

Quan hệ: `parent` (belongsTo chính nó), `children` (hasMany), `products` (hasMany).

### 2.7 `products` — Sản phẩm
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| category_id | bigint unsigned | FK → `product_categories.id` **restrict** + IDX |
| name | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ + IDX |
| sku | varchar(255) | UQ, nullable |
| short_description | text | nullable |
| description | longtext | nullable |
| technical_specifications | json/text | nullable — bảng thông số kỹ thuật |
| price | bigint | nullable — VNĐ; **NULL = "Liên hệ"** + IDX (sort) |
| price_label | varchar(255) | nullable — vd "Giá tham khảo" |
| thumbnail | varchar(255) | nullable — path ảnh đại diện |
| status | enum('draft','published') | default 'draft' + IDX |
| featured | tinyint(1) | default false + IDX |
| sort_order | int | default 0 |
| meta_title / meta_description / meta_keywords | varchar/text | nullable |
| created_at / updated_at | timestamp | IDX trên created_at |

Quan hệ: `category` (belongsTo), `images` (hasMany → `product_images`), `seoMeta` (morphOne).

### 2.8 `product_images` — Ảnh phụ của sản phẩm
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| product_id | bigint unsigned | FK → `products.id` **cascade** + IDX |
| path | varchar(255) | NOT NULL — path trong storage |
| url | varchar(255) | NOT NULL — URL public `/storage/...` |
| filename | varchar(255) | NOT NULL |
| mime_type | varchar(255) | nullable |
| size | int | nullable — bytes |
| alt | varchar(255) | nullable |
| sort_order | int | default 0 |
| created_at / updated_at | timestamp | |

### 2.9 `post_categories` — Danh mục bài viết
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ + IDX |
| description | text | nullable |
| status | varchar(50) | default 'published' + IDX |
| sort_order | int | default 0 |
| created_at / updated_at | timestamp | |

### 2.10 `posts` — Bài viết
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| category_id | bigint unsigned | nullable, FK → `post_categories.id` **nullOnDelete** + IDX |
| user_id | bigint unsigned | FK → `users.id` (tác giả) |
| title | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ + IDX |
| excerpt | text | nullable |
| content | longtext | nullable |
| featured_image | varchar(255) | nullable |
| status | enum('draft','published') | default 'draft' + IDX |
| published_at | timestamp | nullable + IDX |
| meta_title / meta_description / meta_keywords | varchar/text | nullable |
| created_at / updated_at | timestamp | |

Quan hệ: `category` (belongsTo), `author` (belongsTo `users` qua user_id), `seoMeta` (morphOne).

### 2.11 `pages` — Trang tĩnh
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| title | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ + IDX |
| excerpt | text | nullable |
| content | longtext | nullable |
| featured_image | varchar(255) | nullable |
| status | varchar(50) | default 'published' + IDX |
| sort_order | int | default 0 |
| meta_title / meta_description | varchar/text | nullable |
| created_at / updated_at | timestamp | |

### 2.12 `menus` — Menu (header/footer)
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | NOT NULL |
| slug | varchar(255) | UQ |
| location | varchar(50) | `header` / `footer` + IDX |
| created_at / updated_at | timestamp | |

### 2.13 `menu_items` — Item của menu (cây)
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| menu_id | bigint unsigned | FK → `menus.id` **cascade** + IDX |
| parent_id | bigint unsigned | nullable, FK → `menu_items.id` **cascade** + IDX |
| title | varchar(255) | NOT NULL |
| type | enum('url','page') | default 'url' |
| url | varchar(255) | nullable — dùng khi type='url' |
| page_id | bigint unsigned | nullable, FK → `pages.id` **nullOnDelete** — dùng khi type='page' |
| target | varchar(10) | default '_self' |
| sort_order | int | default 0 |
| is_active | tinyint(1) | default true |
| created_at / updated_at | timestamp | |

### 2.14 `banners` — Banner trang chủ
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| title | varchar(255) | NOT NULL |
| subtitle | text | nullable |
| image_desktop | varchar(255) | nullable |
| image_mobile | varchar(255) | nullable |
| link | varchar(255) | nullable |
| button_text | varchar(255) | nullable |
| sort_order | int | default 0 + IDX |
| is_active | tinyint(1) | default true + IDX |
| created_at / updated_at | timestamp | |

### 2.15 `media` — Thư viện media
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| filename | varchar(255) | NOT NULL — tên file ngẫu nhiên |
| path | varchar(255) | NOT NULL — path trong `storage/app/public` |
| url | varchar(255) | NOT NULL — URL public `/storage/<path>` |
| mime_type | varchar(255) | NOT NULL + IDX |
| size | int | NOT NULL — bytes |
| width / height | int | nullable — dimensions ảnh |
| alt | varchar(255) | nullable |
| disk | varchar(50) | default 'public' |
| created_by | bigint unsigned | nullable, FK → `users.id` **nullOnDelete** |
| created_at / updated_at | timestamp | IDX trên created_at |

> DB chỉ lưu metadata; file vật lý nằm trong volume `api_storage` / `cms_storage`
> tại `storage/app/public/...`, serve qua `/storage/`.

### 2.16 `settings` — Cài đặt key-value
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| `key` | varchar(255) | UQ + IDX |
| value | text | nullable |
| `group` | varchar(50) | default 'general' + IDX — `general`, `contact`, `social`, `seo` |
| created_at / updated_at | timestamp | |

### 2.17 `contact_messages` — Tin nhắn liên hệ
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| name | varchar(255) | NOT NULL |
| phone | varchar(50) | NOT NULL |
| email | varchar(255) | nullable |
| subject | varchar(255) | nullable |
| message | text | NOT NULL |
| is_read | tinyint(1) | default false + IDX |
| read_at | timestamp | nullable |
| created_at / updated_at | timestamp | IDX trên created_at |

### 2.18 `seo_meta` — SEO theo từng resource (polymorphic)
| Cột | Kiểu | Ràng buộc |
|-----|------|-----------|
| id | bigint unsigned | PK |
| metable_type | varchar(255) | NOT NULL — vd `App\Models\Product` |
| metable_id | bigint unsigned | NOT NULL — IDX chung (metable_type, metable_id) |
| meta_title / meta_description / meta_keywords | varchar/text | nullable |
| og_title / og_description / og_image | varchar/text | nullable |
| canonical_url | varchar(255) | nullable |
| robots | varchar(255) | nullable |
| created_at / updated_at | timestamp | |

## 3. Tổng hợp indexes

| Bảng | Unique | Index thường |
|------|--------|--------------|
| users | email | — |
| roles | name | — |
| permissions | name | — |
| role_user | PK(role_id, user_id) | — |
| permission_role | PK(permission_id, role_id) | — |
| product_categories | slug | slug, status, parent_id |
| products | slug, sku | slug, status, category_id, featured, created_at |
| product_images | — | product_id |
| post_categories | slug | slug, status |
| posts | slug | slug, status, published_at, category_id |
| pages | slug | slug, status |
| menus | slug | location |
| menu_items | — | menu_id, parent_id |
| banners | — | is_active, sort_order |
| media | — | mime_type, created_at |
| settings | key | key, group |
| contact_messages | — | is_read, created_at |
| seo_meta | — | (metable_type, metable_id) |

## 4. Seed data bắt buộc (tham khảo nhanh)

Seed do **api** chạy khi bảng `users` còn trống (entrypoint tự động):

- **Roles**: `super-admin` (Super Admin), `admin` (Admin), `editor` (Editor); 55 permissions gán đủ cho super-admin.
- **User**: `admin@example.com` / `Admin123!`, role super-admin, `must_change_password = true`.
- **Danh mục sản phẩm**: `camera-giam-sat`, `linh-kien-pc`, `thiet-bi-dien-tu`.
- **Danh mục bài viết**: `khuyen-mai`, `pc-build`, `huong-dan`.
- **8 sản phẩm** published (4 đầu featured) — xem chi tiết trong `docs/CONTRACTS.md` mục 2.2.
- **3 bài viết** published, **4 pages** (`gioi-thieu`, `dich-vu`, `lien-he`, `trang-chu` meta).
- **Menus**: `header` (Trang chủ, Sản phẩm, Camera, Linh kiện PC, Tin tức, Liên hệ), `footer` (3 cột).
- **1 hero banner** active + **settings** các group general/contact/social/seo.

## 5. Lưu ý vận hành

1. **Một database duy nhất**: cả api và cms trỏ `DB_HOST=mysql`, `DB_DATABASE=tkpro`. Bảng `migrations` chung nên `php artisan migrate` ở container nào cũng an toàn.
2. **Migrations do API sở hữu**: CMS luôn dùng schema API đã tạo, kể cả khi dev local.
3. **Backup**: backup volume `mysql_data` (xem `docs/DEPLOYMENT.md`).
4. **Giá NULL** ở `products.price` nghĩa là "Liên hệ" — frontend hiển thị "Liên hệ" thay vì số tiền.
5. Không lưu giỏ hàng / đơn hàng / thanh toán — website chỉ giới thiệu sản phẩm (theo yêu cầu).

# TKPRO — Website giới thiệu sản phẩm điện tử / camera / linh kiện PC

Full-stack rebuild theo kiến trúc:

| Thành phần | Công nghệ | Port | Vai trò |
|------------|-----------|------|---------|
| `frontend/` | Next.js | 3000 | Website bán hàng (không giỏ hàng, không thanh toán) |
| `api/` | Laravel (`/api/v1`, Sanctum, MySQL) | 8000 | REST API + sở hữu migrations/seeds |
| `cms/` | Laravel (`/admin`) | 8001 | Trang quản trị (Blade + Tailwind, session auth) |
| `mysql` | MySQL 8 | 3306 | Database **chung** `tkpro` cho api + cms |
| `nginx` | Nginx reverse proxy | 80 | Cửa ngõ duy nhất phía browser |

```
Browser → nginx:80 ─┬─ /            → frontend:3000
                    ├─ /api/*       → api:8000      (giữ nguyên path /api/...)
                    ├─ /admin/*     → cms:8001      (giữ nguyên path /admin/...)
                    └─ /storage/*   → api:8000      (file tĩnh upload)
```

Tài liệu liên quan: `docs/CONTRACTS.md` (contract tích hợp), `docs/API.md`,
`docs/ERD.md`, `docs/DEPLOYMENT.md`.

---

## 1. Requirements

- **Docker** ≥ 24 + **Docker Compose** plugin v2 (khuyến nghị cho chạy full stack).
- Hoặc chạy lẻ từng phần:
  - Node.js ≥ 22.13 + npm (frontend)
  - PHP ≥ 8.2 + Composer 2 + các extension: `pdo_mysql mbstring exif pcntl bcmath zip gd xml intl fileinfo` (api, cms)
  - MySQL 8 (local hoặc dùng service mysql của compose)
- Git.

## 2. Installation

```bash
git clone <repo-url> tkpro && cd tkpro

# File môi trường (copy từ mẫu rồi điền giá trị)
cp .env.example .env
cp api/.env.example api/.env
cp cms/.env.example cms/.env
cp frontend/.env.example frontend/.env.local   # khi chạy local

# Sinh APP_KEY (api và cms DÙNG CHUNG một key)
cd api && composer install && php artisan key:generate --show   # copy chuỗi base64:...
# paste vào APP_KEY trong: .env (root), api/.env, cms/.env
```

## 3. Environment variables

### 3.1 Root `.env` (dùng cho docker-compose)

| Biến | Mô tả | Ví dụ |
|------|-------|-------|
| `DB_PASSWORD` | Mật khẩu MySQL user `tkpro` (dùng chung api + cms) | `tkpro_secret` → production phải đổi |
| `DB_ROOT_PASSWORD` | Mật khẩu root MySQL (healthcheck/admin) | `root_secret` → production phải đổi |
| `APP_KEY` | Key Laravel dùng chung api + cms | `base64:...` (bắt buộc, không để trống) |
| `NEXT_PUBLIC_API_URL` | URL browser gọi API (build-time frontend) | `http://localhost/api/v1` |
| `SITE_URL` | Origin website, dùng cho SEO và storage | `http://localhost` |
| `API_APP_URL`, `CMS_APP_URL` | Origin Laravel, không thêm `/api` hoặc `/admin` | `http://localhost` |
| `SESSION_DOMAIN` | Để trống để cookie theo host hiện tại | trống |
| `SESSION_SECURE_COOKIE` | Bật khi public site dùng HTTPS | `false` local, `true` production |
| `SANCTUM_STATEFUL_DOMAINS` | Host frontend được phép | `localhost` |
| `HTTP_BIND`, `HTTP_PORT` | Địa chỉ/cổng Nginx; khi có Nginx host dùng `127.0.0.1:8080` | `0.0.0.0`, `80` |

### 3.2 `frontend/.env.local`

| Biến | Local | Docker |
|------|-------|--------|
| `NEXT_PUBLIC_API_URL` | `http://localhost:8000/api/v1` | `http://localhost/api/v1` (qua nginx) |
| `API_INTERNAL_URL` | _(bỏ trống — fallback theo trên)_ | `http://api:8000/api/v1` (mạng nội bộ compose) |

### 3.3 `api/.env` (xem `api/.env.example` đầy đủ)

`APP_NAME="TKPRO API"`, `APP_URL=http://localhost:8000`, `DB_HOST=127.0.0.1` (local)
hoặc `mysql` (docker), `DB_DATABASE=tkpro`, `FRONTEND_URL=http://localhost:3000`,
`SANCTUM_STATEFUL_DOMAINS=localhost:3000`.

### 3.4 `cms/.env` (xem `cms/.env.example` đầy đủ)

`APP_NAME="TKPRO CMS"`, `APP_URL=http://localhost:8001`,
`ADMIN_PATH_PREFIX=admin`, `DB_HOST=127.0.0.1` (local) hoặc `mysql` (docker),
`SESSION_DOMAIN=` (cookie theo host hiện tại).

## 4. Database setup

- Database: `tkpro`, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- **API và CMS dùng chung database. Chỉ API sở hữu migrations và seeds.**
  CMS không chứa migration tạo bảng; luôn khởi tạo/nâng schema bằng `api`.

Tạo DB khi chạy local:
```sql
CREATE DATABASE tkpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tkpro'@'%' IDENTIFIED BY '<mat-khau>';
GRANT ALL PRIVILEGES ON tkpro.* TO 'tkpro'@'%';
```

Khi chạy docker: service `mysql` tự tạo DB + user theo `MYSQL_DATABASE`/`MYSQL_USER`/`MYSQL_PASSWORD`.

## 5. Migration

```bash
# Cách 1 — docker (tự chạy trong entrypoint của api; CMS chờ schema sẵn sàng)
docker compose up -d

# Cách 2 — chạy tay trong container
docker compose exec api php artisan migrate --force

# Cách 3 — chạy local (MySQL local đang chạy, api/.env đã cấu hình)
cd api && php artisan migrate
```

## 6. Seeder

Seed chạy **tự động** khi container `api` khởi động và bảng `users` còn trống
(`php artisan db:seed --force`). Chạy tay:

```bash
docker compose exec api php artisan db:seed --force   # docker
cd api && php artisan db:seed                        # local
```

Seed bao gồm: 3 roles + 60 permissions, user `admin@example.com` / `Admin123!`
(`must_change_password = true`), 3 danh mục sản phẩm, 3 danh mục bài viết,
8 sản phẩm, 3 bài viết, 4 pages, menus header/footer, 1 hero banner, settings.
Chi tiết xem `docs/ERD.md` mục 4 và `docs/CONTRACTS.md` mục 2.2.

## 7. Start development

### Cách A — Docker (khuyến nghị, một lệnh chạy cả stack)

```bash
docker compose up -d --build
docker compose ps          # kiểm tra 5 services đều Up
docker compose logs -f api # xem migrate + seed lần đầu
```

Truy cập:
- Website: http://localhost
- API: http://localhost/api/v1/settings (hoặc trực tiếp http://localhost:8000/api/v1/...)
- CMS: http://localhost/admin (hoặc trực tiếp http://localhost:8001/admin)
- MySQL: `localhost:3306` (user `tkpro` / pass trong `.env`)

Dừng: `docker compose down` (giữ data) · `docker compose down -v` (xóa cả data, seed lại từ đầu).

### Cách B — Chạy lẻ từng phần (không docker)

Cần MySQL local đang chạy + DB `tkpro` đã tạo (mục 4), mỗi `.env` trỏ `DB_HOST=127.0.0.1`.

```bash
# Terminal 1 — MySQL (nếu chưa có): hoặc dùng service mysql của compose:
docker compose up -d mysql

# Terminal 2 — API (http://localhost:8000)
cd api
composer install
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force   # lần đầu
php artisan serve --port=8000

# Terminal 3 — CMS (http://localhost:8001/admin)
cd cms
composer install
# Đặt PUBLIC_STORAGE_PATH trong cms/.env tới đường dẫn tuyệt đối api/storage/app/public
# PUBLIC_STORAGE_URL=http://localhost:8000/storage; APP_KEY giống API
php artisan storage:link --force
php artisan serve --port=8001

# Terminal 4 — Frontend (http://localhost:3000)
cd frontend
npm install
npm run dev
# frontend/.env.local: NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

> Khi chạy lẻ, truy cập CMS trực tiếp `http://localhost:8001/admin`
> (không qua nginx nên không cần rewrite gì thêm).

## 8. Build production

```bash
# Frontend: NEXT_PUBLIC_* nhúng vào bundle lúc build → truyền đúng domain
cd frontend
NEXT_PUBLIC_SITE_URL=https://tkpro.vn NEXT_PUBLIC_API_URL=https://tkpro.vn/api/v1 npm run build
npm run start -- -p 3000

# API / CMS: optimize cho production
cd api && composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
cd ../cms && composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Với docker, build production chỉ là:
```bash
docker compose up -d --build   # entrypoint tự composer install --no-dev, migrate, seed (nếu DB trống)
```

## 9. Docker deployment

Triển khai production trên VPS — xem hướng dẫn chi tiết từng bước trong
**`docs/DEPLOYMENT.md`** (chuẩn bị VPS, DNS, `docker compose up -d --build`,
domain + SSL certbot, checklist bảo mật, backup/restore, vận hành).

Tóm tắt lệnh:
```bash
cp .env.example .env && nano .env   # điền APP_KEY, DB_PASSWORD..., NEXT_PUBLIC_API_URL=https://<domain>/api/v1
docker compose up -d --build
docker compose ps
```

## 10. CMS login

- URL: http://localhost/admin (docker qua nginx) hoặc http://localhost:8001/admin (trực tiếp)
- Tài khoản seed mặc định:
  - Email: `admin@example.com`
  - Mật khẩu: `Admin123!`
- ⚠️ **BẮT BUỘC đổi mật khẩu ngay sau lần đăng nhập đầu tiên** — hệ thống ép đổi
  vì `must_change_password = true`. Không dùng mật khẩu mặc định ở production.

CMS dùng **session auth** (không dùng token API), giao diện tiếng Việt,
quản lý: sản phẩm, danh mục, bài viết, trang tĩnh, banner, menu, media,
settings, users/roles, hộp thư liên hệ.

## 11. API documentation

- REST endpoints đầy đủ: **`docs/API.md`** (public, auth, admin; envelope, params, ví dụ response, mã lỗi).
- Machine-readable: `docs/openapi.yaml` (do worker api cung cấp — trỏ tới file này khi có).
- Base URL: dev `http://localhost:8000/api/v1` · qua nginx `http://localhost/api/v1`.

## 12. Troubleshooting

| Vấn đề | Nguyên nhân thường gặp | Cách xử lý |
|--------|------------------------|------------|
| `docker compose up` báo api/cms restart liên tục | Chưa kết nối được MySQL | `docker compose logs api`; chờ healthcheck `mysql` xanh (`docker compose ps`); kiểm tra `DB_PASSWORD` khớp giữa `.env` root và service mysql |
| `SQLSTATE[HY000] [2002] Connection refused` | Sai `DB_HOST` | Trong docker phải là `DB_HOST=mysql`; chạy local là `127.0.0.1` |
| `/admin` trắng trang hoặc 404 | CMS chưa nhận prefix | Kiểm tra nginx route `/admin/` → `cms:8001` (`docker compose logs nginx`); cms phải đăng ký toàn bộ route dưới prefix `/admin` (`ADMIN_PATH_PREFIX=admin`) |
| Ảnh `/storage/...` 404 | Chưa link storage / sai volume | `docker compose exec api php artisan storage:link`; kiểm tra API/CMS cùng mount `api_storage` |
| Frontend gọi API lỗi CORS | Sai origin cho phép | `api/.env`: `FRONTEND_URL` và `SANCTUM_STATEFUL_DOMAINS` phải khớp domain frontend |
| `419 Page Expired` ở CMS | Sai session domain | Để `SESSION_DOMAIN` trống; dùng root `.env` khi chạy Compose |
| Upload báo 413 | Vượt giới hạn body | nginx đã set `client_max_body_size 10M`; kiểm tra thêm `upload_max_filesize`/`post_max_size` của PHP |
| Login API 429 | Vượt rate limit | Login giới hạn 5 req/phút/IP — đợi 1 phút rồi thử lại |
| Quên mật khẩu admin CMS | — | `docker compose exec cms php artisan tinker` → tìm user, `Hash::make()` pass mới, set `must_change_password=true` |
| Port 80/3000/8000/8001/3306 đã dùng | Xung đột port | Đổi port mapping phía host trong `docker-compose.yml` (vd `"8080:80"`) |
| Muốn reset DB về seed ban đầu | — | `docker compose down -v` (⚠️ xóa hết data) rồi `docker compose up -d --build` |

---

**Brand**: TKPRO · Xanh `#087a3b` / `#065d2d` · Đỏ CTA `#e32121` · Nền `#f6faf7` · Chữ `#10231a`.
Website chỉ giới thiệu sản phẩm — **tuyệt đối không giỏ hàng / thanh toán / order**.

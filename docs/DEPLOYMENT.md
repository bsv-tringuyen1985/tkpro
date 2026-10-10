# TKPRO — Deployment Guide

Hướng dẫn triển khai TKPRO lên production (VPS) bằng Docker Compose.

## 1. Tổng quan kiến trúc production

```
Internet → (443/80) nginx container ─┬─ /            → frontend:3000 (Next.js)
                                     ├─ /api/*       → api:8000      (Laravel API)
                                     ├─ /admin/*     → cms:8001      (Laravel CMS)
                                     └─ /storage/*   → api:8000      (file upload)
                                     MySQL 8 (chỉ nội bộ, không expose ra ngoài nếu không cần)
```

## 2. Chuẩn bị VPS

Yêu cầu tối thiểu: **2 CPU, 4GB RAM, 20GB SSD**, Ubuntu 22.04+.

```bash
# Cập nhật hệ thống
sudo apt update && sudo apt upgrade -y

# Cài Docker + Compose plugin
sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update && sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Cho user hiện tại chạy docker không cần sudo (logout/login lại sau bước này)
sudo usermod -aG docker $USER
```

Trỏ DNS: tạo bản ghi A `tkpro.vn` (và `www.tkpro.vn`) → IP của VPS.

## 3. Deploy lần đầu

```bash
# 1. Lấy code lên VPS
git clone <repo-url> /opt/tkpro && cd /opt/tkpro

# 2. Tạo file môi trường từ mẫu
cp .env.example .env
cp api/.env.example api/.env
cp cms/.env.example cms/.env
cp frontend/.env.example frontend/.env.local   # chỉ dùng khi build local

# 3. Điền giá trị production vào .env (root)
#    - APP_KEY: chạy tạm container php để generate (bước 4), hoặc generate local rồi paste
#    - DB_PASSWORD / DB_ROOT_PASSWORD: chuỗi ngẫu nhiên dài (vd: openssl rand -base64 32)
#    - NEXT_PUBLIC_API_URL: https://tkpro.vn/api/v1
nano .env

# 4. Generate APP_KEY (dùng chung cho api + cms)
docker run --rm php:8.2-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
#    → copy chuỗi base64:... vào APP_KEY trong .env (root)

# 5. Cấu hình tất cả giá trị Compose trong .env ở root (không dùng api/.env hoặc cms/.env):
# SITE_URL=https://tkpro.vn
# API_APP_URL=https://tkpro.vn
# CMS_APP_URL=https://tkpro.vn
# NEXT_PUBLIC_API_URL=https://tkpro.vn/api/v1
# SESSION_DOMAIN=
# SESSION_SECURE_COOKIE=true
# SANCTUM_STATEFUL_DOMAINS=tkpro.vn
# Nếu dùng Nginx host ở mục 4: HTTP_BIND=127.0.0.1, HTTP_PORT=8080

# 6. Build & chạy
docker compose up -d --build

# 7. Kiểm tra
docker compose ps
docker compose logs -f api   # xem migrate + seed
curl -s http://localhost/api/v1/settings | head -c 300
```

Entrypoint của `api` tự động: chờ MySQL → `composer install` (nếu thiếu vendor)
→ `storage:link` → `migrate --force` → **seed khi bảng users trống** → chạy HTTP server trên cổng 8000.
Lần deploy đầu tiên sẽ tự seed đầy đủ dữ liệu mẫu (xem `docs/ERD.md` mục 4).

## 4. Domain + SSL (HTTPS)

Khuyến nghị dùng **Caddy** hoặc **Nginx ngoài** làm TLS terminator trước compose,
hoặc đơn giản nhất: chạy thêm container nginx-proxy + acme-companion.

### Cách đơn giản: Nginx trên host (TLS termination)

```nginx
# /etc/nginx/sites-available/tkpro.vn
server {
    listen 80;
    server_name tkpro.vn www.tkpro.vn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tkpro.vn www.tkpro.vn;

    ssl_certificate     /etc/letsencrypt/live/tkpro.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tkpro.vn/privkey.pem;

    client_max_body_size 10M;

    location / {
        proxy_pass http://127.0.0.1:8080;   # nginx container của compose
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

Cấp chứng chỉ miễn phí:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tkpro.vn -d www.tkpro.vn
# Tự gia hạn: certbot cài sẵn timer systemd, kiểm tra bằng:
sudo certbot renew --dry-run
```

Sau khi có HTTPS, đặt các biến domain ở root `.env` như mục 3 rồi chạy
`docker compose up -d --build`. `APP_URL` là origin, không thêm `/api` hoặc `/admin`.
Laravel tạo URL HTTPS theo cấu hình APP_URL, kể cả khi proxy nội bộ dùng HTTP.

> Lưu ý: `NEXT_PUBLIC_*` được nhúng vào bundle lúc **build**, nên đổi domain
> phải build lại image frontend.

## 5. Bảo mật bắt buộc sau deploy

1. **Đổi mật khẩu admin**: đăng nhập `https://tkpro.vn/admin` bằng
   `admin@example.com` / `Admin123!` → hệ thống ép đổi mật khẩu ngay lần đầu
   (cờ `must_change_password`). Đặt mật khẩu mạnh, bật nếu có 2FA.
2. **APP_KEY**: phải là key riêng, ngẫu nhiên — KHÔNG dùng key mẫu/dev.
3. **DB_PASSWORD / DB_ROOT_PASSWORD**: chuỗi ngẫu nhiên dài, khác nhau.
4. **APP_DEBUG=false** ở cả api và cms (production).
5. **Đóng port MySQL**: bỏ dòng `ports: ["3306:3306"]` của service mysql trong
   `docker-compose.yml` (hoặc override file production) nếu không cần truy cập từ ngoài.
6. File `.env` các cấp: `chmod 600`, không commit vào git.
7. Cập nhật `SANCTUM_STATEFUL_DOMAINS` và `FRONTEND_URL`/`SESSION_DOMAIN` theo domain thật.

## 6. Backup & restore

### 6.1 Backup database (khuyến nghị: cron mỗi đêm)

```bash
# Backup thủ công
docker compose exec mysql mysqldump -u root -p"$DB_ROOT_PASSWORD" \
  --single-transaction --routines tkpro | gzip > /opt/backups/tkpro-$(date +%F).sql.gz

# Hoặc backup cả volume mysql_data
docker run --rm -v tkpro_mysql_data:/data -v /opt/backups:/backup \
  alpine tar czf /backup/mysql_data-$(date +%F).tar.gz -C /data .
```

Ví dụ cron (chạy 2h sáng mỗi ngày, giữ 14 bản):
```cron
0 2 * * * /opt/tkpro/scripts/backup-db.sh >> /var/log/tkpro-backup.log 2>&1
```

Nội dung gợi ý `scripts/backup-db.sh`:
```bash
#!/bin/bash
set -e
cd /opt/tkpro
set -a; source .env; set +a
mkdir -p /opt/backups
docker compose exec -T mysql mysqldump -u root -p"$DB_ROOT_PASSWORD" \
  --single-transaction tkpro | gzip > "/opt/backups/tkpro-$(date +%F).sql.gz"
find /opt/backups -name 'tkpro-*.sql.gz' -mtime +14 -delete
```

### 6.2 Backup file upload
Volume `api_storage` (và `cms_storage`) chứa `storage/app/public`:
```bash
docker run --rm -v tkpro_api_storage:/data -v /opt/backups:/backup \
  alpine tar czf /backup/api_storage-$(date +%F).tar.gz -C /data .
```

### 6.3 Restore database
```bash
gunzip -c /opt/backups/tkpro-2026-10-07.sql.gz | \
  docker compose exec -T mysql mysql -u root -p"$DB_ROOT_PASSWORD" tkpro
```

## 7. Vận hành thường ngày

```bash
cd /opt/tkpro

# Xem trạng thái & logs
docker compose ps
docker compose logs -f --tail=100 api
docker compose logs -f --tail=100 nginx

# Update code mới (không mất dữ liệu — volumes giữ nguyên)
git pull
docker compose up -d --build

# Chạy artisan trong container api
docker compose exec api php artisan migrate --force
docker compose exec api php artisan cache:clear
docker compose exec api php artisan tinker

# Vào MySQL
docker compose exec mysql mysql -u tkpro -p tkpro

# Restart một service
docker compose restart cms

# Dọn image cũ sau nhiều lần build
docker image prune -f
```

### Rollback nhanh
```bash
git checkout <tag-cu>
docker compose up -d --build
# Nếu migration mới đã chạy, cần rollback DB:
docker compose exec api php artisan migrate:rollback --force
```

## 8. Checklist deploy production

- [ ] `.env` (root) đã điền: `APP_KEY` riêng, `DB_PASSWORD`/`DB_ROOT_PASSWORD` ngẫu nhiên, `NEXT_PUBLIC_API_URL=https://<domain>/api/v1`
- [ ] Root `.env`: SITE_URL, API_APP_URL, CMS_APP_URL và NEXT_PUBLIC_API_URL đúng domain, SESSION_SECURE_COOKIE=true.
- [ ] `docker compose up -d --build` chạy thành công, `docker compose ps` tất cả `Up`
- [ ] `curl https://<domain>/api/v1/settings` trả JSON envelope `{"success":true,...}`
- [ ] `https://<domain>/admin` mở được trang login CMS
- [ ] Đăng nhập admin lần đầu và **đổi mật khẩu** (`admin@example.com` / `Admin123!`)
- [ ] SSL hợp lệ, tự gia hạn certbot hoạt động
- [ ] Cron backup DB mỗi đêm, đã test restore 1 lần
- [ ] Port 3306 không expose ra internet (kiểm tra `ss -tlnp`)
- [ ] `APP_DEBUG=false`, không lộ stack trace khi lỗi
- [ ] File `.env` các cấp `chmod 600`, không có trong git (`git status` sạch)

## 9. Xử lý sự cố nhanh

| Triệu chứng | Kiểm tra / xử lý |
|-------------|------------------|
| `api` restart liên tục | `docker compose logs api` — thường do chưa kết nối MySQL; chờ healthcheck `mysql` xanh |
| Trắng trang `/admin` 404 | Kiểm tra nginx route `/admin/` → `cms:8001`; cms phải đăng ký route dưới prefix `/admin` |
| Ảnh `/storage/...` 404 | `docker compose exec api php artisan storage:link`; kiểm tra volume `api_storage` đã mount |
| 500 khi upload | `client_max_body_size 10M` ở nginx; kiểm tra quyền ghi `storage/` trong container |
| Frontend gọi API lỗi CORS | Kiểm tra `FRONTEND_URL`/`SANCTUM_STATEFUL_DOMAINS` ở api khớp domain thật |
| Quên mật khẩu admin | `docker compose exec cms php artisan tinker` → reset password + `must_change_password=true` |

Chi tiết hơn xem mục Troubleshooting trong `README.md`.

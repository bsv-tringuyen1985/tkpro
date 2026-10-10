# Chạy local trên Windows

Khởi động từ thư mục project:

```powershell
node scripts/start-local.cjs
```

Launcher chạy API, CMS và build/start frontend dưới dạng background process:

- Website: http://127.0.0.1:13000
- API: http://127.0.0.1:18000/api/v1
- CMS: http://127.0.0.1:18001/admin

Tài khoản mẫu: `admin@example.com` / `Admin123!`. CMS yêu cầu đổi mật khẩu lần đầu.

Database local được lưu bền vững tại `.runtime/local.sqlite`, API và CMS dùng chung file này.
Chỉ seed khi chưa có user; khởi động lại không reset dữ liệu. Ảnh dùng chung `api/storage/app/public`.
Launcher truyền môi trường riêng cho các process; không sửa các file `.env` hoặc cấu hình MySQL/Docker.

Yêu cầu: Node 22.13+, PHP có `pdo_sqlite` (launcher bật extension qua `-d`),
đã cài dependencies trong `api/vendor`, `cms/vendor`, `frontend/node_modules`.
Có thể đặt `PHP_BINARY` để chọn đường dẫn PHP khác.

Dừng ba process do launcher quản lý, giữ database:

```powershell
node scripts/stop-local.cjs
```

Log và PID lưu trong `.runtime/`, được bỏ qua khi đóng gói Docker và commit Git.
Sau khi sửa source, dừng rồi chạy lại launcher để build frontend mới.
Các cổng phải trống; launcher không dừng ứng dụng khác để lấy cổng.

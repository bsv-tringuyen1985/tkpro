# Kiểm tra bản frontend tĩnh — 11/10/2026

## Kết quả

- `npm run build:static`: thành công, tạo `frontend/out/`.
- `npm run typecheck`: thành công.
- `npm test`: 10/10 test thành công (contract API, metadata, routes tĩnh,
  query Unicode, runtime config, ảnh, GET/POST, kiểm tra envelope).
- `git diff --check`: không có lỗi whitespace.

## Kiểm tra trình duyệt trên bản build

Phục vụ trực tiếp `out/` bằng máy chủ file tĩnh local, với API giả lập ở origin
khác và cấu hình CORS. Không gửi form lên server thật. File `config.js` được
máy chủ QA thay thế trong response, không thay đổi file cấu hình giao hàng.

Đã xác nhận:

1. Trang chủ, header/footer, ảnh từ API và bố cục không tràn ngang ở viewport 995px.
2. Sắp xếp giá giảm dần và sang trang 2 giữ query sắp xếp.
3. Trang chi tiết hiển thị thông số, gallery đổi ảnh và canonical đúng URL.
4. Tải lại trực tiếp URL sản phẩm vẫn hiển thị đầy đủ.
5. Link tư vấn mang tên sản phẩm vào form.
6. Form hiển thị lỗi 422 và gửi thành công với dữ liệu giả lập.
7. Tìm kiếm trả về sản phẩm/bài viết; trang chi tiết tin tức hiển thị nội dung.
8. Danh mục, giới thiệu và dịch vụ hiển thị dữ liệu API.
9. Thêm sản phẩm trong API giả lập sau build, mở URL mới và tải lại vẫn chạy.
10. Slug không tồn tại hiển thị trang không tìm thấy và `noindex,follow`.
11. API trả 503 hiển thị lỗi tải trang; khôi phục API và bấm Thử lại thành công.

Có thể chạy lại môi trường QA bằng `node frontend/tests/preview-static.cjs`.
Đây chỉ là fixture local để kiểm thử, không upload file này lên hosting.

## Giới hạn kiểm tra

Chưa triển khai lên server của người dùng. Rewrite `.htaccess`, cấu hình
virtual host LiteSpeed/Apache, SSL, CORS và kết nối database thực tế cần kiểm
tra sau upload. Bản tĩnh dùng JavaScript để render nội dung, không cung cấp
SSR hoặc thẻ SEO riêng từng URL trong HTML ban đầu; xem `CPANEL-STATIC.md`.

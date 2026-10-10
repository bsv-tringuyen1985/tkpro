# Frontend tĩnh — upload trực tiếp, không chạy Node.js trên server

Bản này dùng React trong trình duyệt và API Laravel. Không cần npm, Node.js,
PM2 hay Passenger trên hosting. Giữ nguyên API/CMS và giao diện hiện có.

## Build trên máy cá nhân

Máy build cần Node.js >= 22.13 và npm. Tại thư mục `frontend`:

```bash
npm ci
npm run build:static
```

Kết quả là **`frontend/out/`**, gồm `index.html`, `assets/`, `config.js`,
`.htaccess`, logo/font và `robots.txt`. Build không cần gọi API và không chứa
database, `.env`, APP_KEY hay mật khẩu. Không upload `.next` hoặc `node_modules`.

`npm run build` cũ vẫn dành cho Next.js chạy Node.js / Docker.
`npm run dev:static` và `npm run preview:static` chỉ phục vụ phát triển/xem thử
trên máy cá nhân, không phải lệnh cần chạy trên hosting.

## Cấu hình domain

Sửa `frontend/public/config.js` trước build, hoặc sửa `config.js` trong bản đã
upload; không cần build lại khi đổi URL:

```javascript
window.TKPRO_CONFIG = {
  apiUrl: "http://linhkien-api.tkprocctv.com/api/v1",
  siteUrl: "http://linhkien.tkprocctv.com"
};
```

Hai domain hiện dùng HTTP theo xác nhận của chủ website. Khi bật SSL, đổi cả
hai thành HTTPS. Trang HTTPS không thể gọi API HTTP. Đây là file công khai;
không đặt bất kỳ khóa/mật khẩu nào vào đây. `API_INTERNAL_URL` không dùng trong
bản tĩnh; API phải truy cập được trực tiếp từ trình duyệt.

## Upload và document root

1. Sao lưu nội dung website đang có trước khi thay bản triển khai.
2. Upload **nội dung bên trong `out/`** vào document root của domain frontend,
   ví dụ `/home/ktx.bravesoft.vn/linhkien/frontend-public/` hoặc `public_html/`.
3. Document root phải là thư mục **chứa trực tiếp `index.html`**.
4. Upload cả file ẩn `.htaccess` và toàn bộ `assets/`, font, logo.
5. Website này triển khai ở gốc domain, không phải đường dẫn con `/frontend/`.
6. Không sửa document root của API/CMS: chúng vẫn trỏ vào thư mục `public`
   tương ứng của Laravel. Bản frontend không chứa source PHP.

Nếu server đang có cấu hình ứng dụng Node/Passenger cho domain frontend,
gỡ ánh xạ đó để domain phục vụ file tĩnh. Apache/LiteSpeed cần cho phép rewrite
trong `.htaccess`. OpenLiteSpeed cần bật **Enable Rewrite** và **Auto Load from
.htaccess** cho virtual host rồi reload nếu thay đổi chưa được nhận.
Nếu host báo lỗi directive `Options`, nhờ hosting cho phép hoặc bỏ riêng dòng
`Options -Indexes -MultiViews`; vẫn cần giữ các rewrite rules.

`/san-pham/<danh-muc>/<slug>` và `/tin-tuc/<slug>` luôn tải cùng ứng dụng rồi
lấy dữ liệu từ API. Vì vậy sản phẩm/bài viết tạo mới trong CMS mở được ngay mà
không phải build lại. Các liên kết dùng điều hướng trình duyệt bình thường;
mở tab mới, Back/Forward, query lọc, phân trang và F5 vẫn hoạt động.

## CORS trên API

Đặt trong `.env` của **API**, không phải chỉ CMS:

```dotenv
FRONTEND_URL=http://linhkien.tkprocctv.com
```

Sau đó clear/cache cấu hình bằng **user sở hữu ứng dụng**, không dùng root
để tạo file cache. Ví dụ server hiện tại (chạy từ tài khoản root):

```bash
cd /home/ktx.bravesoft.vn/linhkien/api
runuser -u ktxbr5747 -- php artisan config:clear
runuser -u ktxbr5747 -- php artisan config:cache
```

Khi chuyển HTTPS phải cập nhật origin trong API. Ảnh upload phải truy cập được
qua domain API `/storage/...`; API/CMS cần dùng chung thư mục ảnh như trước.

## SEO và sitemap

Đây là bản dựng giao diện trong trình duyệt: tiêu đề, canonical, metadata SEO
và nội dung được cập nhật sau khi tải API. Bot chia sẻ Facebook/Zalo không chạy
JavaScript có thể chỉ thấy tiêu đề chung; không tương đương SSR về SEO.
Trình duyệt phải bật JavaScript. Slug không tồn tại hiển thị trang không tìm
thấy và `noindex`, nhưng URL chi tiết được rewrite vẫn trả shell HTTP 200.

API hiện có sitemap động tại `http://linhkien-api.tkprocctv.com/sitemap.xml`.
Nếu muốn `/sitemap.xml` trên frontend chuyển tới đó, thêm trước các rewrite
rule khác trong `.htaccess` (đổi HTTPS khi đã bật SSL):

```apache
RewriteRule ^sitemap\.xml$ http://linhkien-api.tkprocctv.com/sitemap.xml [R=302,L]
```

Sau đó thêm `Sitemap: http://linhkien.tkprocctv.com/sitemap.xml` vào `robots.txt`.
Nếu dùng sitemap khác host với công cụ tìm kiếm, cần cấu hình/xác minh cả hai
host theo yêu cầu công cụ. Build không chụp cố định danh sách sản phẩm.

## Kiểm tra sau upload

- Trang chủ hiện nội dung, logo, ảnh từ API.
- Mở link chi tiết sản phẩm bằng tab mới và F5 không bị lỗi route của server.
- Tìm kiếm, lọc danh mục, sắp xếp, phân trang sản phẩm và bài viết hoạt động.
- Link tư vấn giữ đúng tên sản phẩm; thử form bằng thông tin kiểm thử của bạn.
- Nếu hiện thông báo không kết nối được: kiểm tra `config.js`, domain API,
  HTTP/HTTPS, CORS và tab Network trong trình duyệt.
- Nếu trang chủ chạy nhưng link chi tiết 404 từ web server: kiểm tra `.htaccess`
  và rewrite của virtual host; đây không phải lỗi npm.

Khi cập nhật giao diện, build lại và upload các file mới. Khi chỉ cập nhật
nội dung CMS hoặc URL trong `config.js`, không cần build lại.

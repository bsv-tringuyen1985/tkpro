# Rà soát source TKPRO — 08/10/2026

> Cập nhật: 14 phát hiện dưới đây đã được xử lý trong source. Xem `FIXES.md` để biết thay đổi, cách nâng cấp và kết quả kiểm thử. Phần còn lại là bản ghi rà soát trước khi sửa; các log cũ chỉ có giá trị lịch sử. Các script tái hiện lỗi đã được thay bằng regression tests trong `api/tests`, `cms/tests`, `frontend/tests`.

P1 = cần ưu tiên vì làm hỏng luồng chính hoặc vượt quyền. P2 = lỗi chức năng, cấu hình triển khai hoặc kiểm thử cần xử lý tiếp. Các kết luận từ đọc code được phân biệt với các lỗi đã tái hiện.

| Kiểm tra | Kết quả |
|---|---|
| Cú pháp PHP | 247 file PHP, không thấy lỗi cú pháp; không tính cú pháp Blade là đã được kiểm thử |
| `npm run typecheck` | Pass |
| `npm run build` | Pass sau khi chạy ngoài sandbox; lần trong sandbox lỗi phân giải alias, không được coi là lỗi source |
| PHPUnit gốc | 27/27 test lỗi ở setup: `Class "Mockery" not found` |
| Ca xác minh riêng | 5 test / 9 assertions pass: 4 ca chứng minh hành vi sai, 1 ca kiểm tra tác giả null hoạt động đúng |
| Frontend với response giả lập đúng cấu trúc API trong source | Xác nhận đọc sai 3 response chi tiết và menu page trỏ về `/` |
| `npm audit` | 9 package được đánh dấu: 1 critical, 6 high, 2 moderate; đây là mức từ advisory, không phải 9 exploit đã được chứng minh |
| Docker / MySQL / trình duyệt end-to-end | Chưa chạy: Docker daemon hiện không hoạt động. Các test API dùng SQLite trong bộ nhớ, không truy cập DB đang sử dụng |

1. **[P1] Frontend đọc sai response chi tiết sản phẩm, bài viết và danh mục.**

   [productService.ts](C:/Users/MrTri/Documents/tkpro/frontend/services/productService.ts:29) khai báo `getProduct()` trả `Product`, nhưng [API](C:/Users/MrTri/Documents/tkpro/api/app/Http/Controllers/Api/V1/ProductController.php:84) trả `data = { product, related }`. Tương tự, [getPost()](C:/Users/MrTri/Documents/tkpro/frontend/services/postService.ts:26) nhận `{ post, related }`, và [getCategory()](C:/Users/MrTri/Documents/tkpro/frontend/services/productService.ts:41) nhận `{ category, products }`.

   Hậu quả: trang chi tiết đọc `name`, `title`, ảnh, mô tả từ object bao ngoài nên không có dữ liệu; canonical có thể chứa `undefined`. Trang danh mục đọc `category.slug` thành `undefined`, bỏ mất bộ lọc và lấy sản phẩm của tất cả danh mục. Phân trang sinh đường dẫn `/san-pham/undefined`. Generic TypeScript không xác minh JSON thực tế, nên typecheck/build vẫn pass.

   **Đã tái hiện bằng script frontend.** Sửa bằng việc unwrap response trong service, gắn `related` vào dữ liệu mà page sử dụng và thống nhất kiểu dữ liệu với API.

2. **[P1] Script CMS không vượt qua bước chờ MySQL vì escape sai biến PHP.**

   [cms-entrypoint.sh:18](C:/Users/MrTri/Documents/tkpro/docker/cms-entrypoint.sh:18) dùng `catch (Exception \\$e)` bên trong chuỗi shell có dấu ngoặc kép. Shell biến `\\` thành backslash và expand `$e`; khi biến chưa tồn tại, PHP nhận `catch (Exception \)`, sai cú pháp. Lỗi bị chuyển vào `/dev/null`, nên CMS lặp 60 lần rồi thoát, ngay cả khi MySQL sẵn sàng.

   **Đã xác minh chuỗi PHP sau shell expansion bằng hàm PHP giả, không kết nối DB.** Sửa thành cách escape tương tự script API (`\$e`) hoặc chuyển đoạn PHP ra file riêng. Chưa chạy container thật.

3. **[P1] Ảnh upload từ CMS không nằm trong storage mà website phục vụ.**

   [docker-compose.yml:51](C:/Users/MrTri/Documents/tkpro/docker-compose.yml:51) mount `api_storage` vào API; [dòng 84](C:/Users/MrTri/Documents/tkpro/docker-compose.yml:84) mount `cms_storage` riêng vào CMS. [Nginx](C:/Users/MrTri/Documents/tkpro/docker/nginx/default.conf:75) chuyển `/storage/` tới API, trong khi [MediaService](C:/Users/MrTri/Documents/tkpro/cms/app/Support/MediaService.php:30) lưu file ở disk CMS và ghi URL `/storage/...`.

   Hậu quả theo cấu hình: upload có bản ghi DB nhưng URL ảnh từ CMS trả 404 qua nginx; CMS cũng dùng URL đó để preview. Sửa bằng một public-storage volume chung cho API/CMS hoặc một nơi lưu và phục vụ file thống nhất. Kết luận từ đối chiếu cấu hình, chưa chạy Docker thực tế.

4. **[P1] Admin có thể tự nâng quyền thành Super Admin.**

   [API UserController](C:/Users/MrTri/Documents/tkpro/api/app/Http/Controllers/Api/V1/Admin/UserController.php:75) và [CMS UserController](C:/Users/MrTri/Documents/tkpro/cms/app/Http/Controllers/UserController.php:122) sync mọi role ID hợp lệ; không kiểm tra người thao tác có được cấp role `super-admin` hay không. Seeder cấp `users.update` cho Admin. Ngoài ra, [CMS RoleController](C:/Users/MrTri/Documents/tkpro/cms/app/Http/Controllers/RoleController.php:14) dùng quyền `users.*` thay vì `roles.*`, trái với việc seeder dành quản lý vai trò cho Super Admin.

   **Đã tái hiện qua API:** user role Admin gửi `PUT /api/v1/admin/users/{id-cua-minh}` với role ID Super Admin, nhận 200 và trở thành Super Admin. Sửa bằng policy riêng cho gán role, bảo vệ tài khoản/role có quyền cao hơn và dùng đúng quyền `roles.*` ở CMS.

5. **[P1] API bỏ qua yêu cầu đổi mật khẩu bắt buộc.**

   [AuthController](C:/Users/MrTri/Documents/tkpro/api/app/Http/Controllers/Api/V1/AuthController.php:30) vẫn cấp bearer token khi `must_change_password=true`; [admin routes](C:/Users/MrTri/Documents/tkpro/api/routes/api.php:70) chỉ kiểm tra Sanctum và permission. Cơ chế ép đổi mật khẩu chỉ nằm ở CMS.

   **Đã tái hiện:** user Super Admin có cờ này vẫn gọi danh sách người dùng qua API và nhận 200. Tài khoản seed dùng mật khẩu mặc định nên có thể đi vòng qua màn hình ép đổi mật khẩu. Sửa bằng middleware/policy chặn admin API cho tới khi đổi mật khẩu, kèm luồng đổi mật khẩu phù hợp.

6. **[P1, rủi ro dependency] Next.js đang ở phiên bản có advisory bảo mật.**

   [package.json](C:/Users/MrTri/Documents/tkpro/frontend/package.json:1) khóa Next.js `14.2.28`. Audit đánh dấu package này critical; các dependency khác gồm Tailwind/PostCSS và dependency gián tiếp cũng có cảnh báo. Chi tiết được lưu trong [npm-audit-review.json](C:/Users/MrTri/Documents/tkpro/docs/npm-audit-review.json).

   [Advisory chính thức của Next.js](https://github.com/vercel/next.js/security/advisories/GHSA-2xp9-vwfh-vxw4) nêu rủi ro RCE khi xử lý AVIF qua image optimization, với điều kiện thư viện xử lý ảnh liên quan được sử dụng. [Thông báo RSC](https://nextjs.org/blog/security-update-2025-12-11) cũng nêu lỗi DoS liên quan App Router. Source dùng App Router và `next/image`, nên cần ưu tiên đánh giá/nâng cấp; rà soát này không chứng minh khai thác RCE trên deployment cụ thể.

   Sửa bằng nâng lên bản được vá và còn được hỗ trợ, cập nhật dependency liên quan rồi chạy lại build và test. Không chạy `npm audit fix --force` tự động vì audit đề xuất thay đổi major version.

7. **[P2] Không lưu được sửa đổi vai trò hệ thống từ form CMS.**

   [roles/form.blade.php:24](C:/Users/MrTri/Documents/tkpro/cms/resources/views/roles/form.blade.php:24) disable input `name` cho `super-admin`, `admin`, `editor`. Input disabled không được gửi trong form, nhưng [validateData()](C:/Users/MrTri/Documents/tkpro/cms/app/Http/Controllers/RoleController.php:101) luôn yêu cầu `name`. Vì thế đổi tên hiển thị hoặc quyền từ giao diện sẽ bị validation chặn.

   Sửa bằng lấy mã role hiện tại ở server khi sửa core role hoặc dùng input readonly, đồng thời vẫn giữ kiểm tra không được đổi mã. Kết luận từ đối chiếu form và validation.

8. **[P2] Migration CMS không phải bản mirror của API; hướng dẫn chạy local gây xung đột.**

   README nói chạy migrate ở cả hai app an toàn, nhưng hai bộ migration có tên và cách chia file khác nhau. [Migration CMS đầu tiên](C:/Users/MrTri/Documents/tkpro/cms/database/migrations/2026_10_07_000001_create_users_and_auth_tables.php:15) tạo lại `users`, đã được API tạo bằng migration tên khác. Bảng `migrations` chung không giúp bỏ qua file có tên khác.

   **Đã tái hiện trên SQLite:** áp dụng migration CMS sau schema API ném `QueryException`. Script Docker CMS hiện không migrate, nên lỗi này áp dụng khi làm theo hướng dẫn local/manual. Sửa bằng API sở hữu schema duy nhất và bỏ bước migrate CMS trong tài liệu, hoặc đồng bộ đúng bộ migration.

9. **[P2] Menu loại page bị chuyển về trang chủ.**

   [MenuItemResource](C:/Users/MrTri/Documents/tkpro/api/app/Http/Resources/MenuItemResource.php:17) trả URL đã resolve trong trường `url`, không trả `page_slug`. [resolveMenuItemUrl()](C:/Users/MrTri/Documents/tkpro/frontend/services/menuService.ts:11) lại yêu cầu `page_slug` cho loại page và trả `/` nếu thiếu, bỏ qua URL hợp lệ từ API.

   **Đã tái hiện:** `{ type: 'page', url: '/gioi-thieu' }` được resolve thành `/`. Sửa bằng sử dụng URL API đã resolve hoặc trả/chuẩn hóa cùng một contract.

10. **[P2] SEO nhập trong CMS không đi tới metadata frontend.**

    CMS lưu `meta_title`, `meta_description`, `meta_keywords` trực tiếp vào product/post/page. [Resource API](C:/Users/MrTri/Documents/tkpro/api/app/Http/Resources/ProductResource.php:32) trả khối `seo` từ quan hệ `seo_meta` và bỏ các cột SEO trực tiếp; [buildMetadata()](C:/Users/MrTri/Documents/tkpro/frontend/lib/metadata.ts:24) lại chỉ đọc các trường SEO ở cấp ngoài, không đọc `seo`.

    Do đó cả nội dung SEO cập nhật từ CMS lẫn khối SEO qua API không được frontend sử dụng đúng. Sửa bằng một nguồn SEO thống nhất và chuẩn hóa dữ liệu tại resource/service. Kết luận từ luồng đọc/ghi source.

11. **[P2] Cài đặt mặc định khiến form cài đặt không lưu được.**

    [Seeder](C:/Users/MrTri/Documents/tkpro/api/database/seeders/SettingSeeder.php:24) lưu Facebook/YouTube là `#`, nhưng [SettingController](C:/Users/MrTri/Documents/tkpro/cms/app/Http/Controllers/SettingController.php:89) validate hai trường là URL. Form gửi toàn bộ cài đặt nên chỉ sửa hotline vẫn bị chặn nếu các giá trị mặc định chưa được thay/xóa; input HTML loại URL cũng không chấp nhận `#`.

    Sửa bằng seed giá trị rỗng/null hoặc URL thật và thống nhất quy tắc nhập liệu.

12. **[P2] Bộ test API không chạy được với dependency đã khai báo.**

    [composer.json](C:/Users/MrTri/Documents/tkpro/api/composer.json:1) thiếu `mockery/mockery` trong require-dev. [FeatureTestCase](C:/Users/MrTri/Documents/tkpro/api/tests/Feature/FeatureTestCase.php:17) dùng RefreshDatabase và seed qua cơ chế console mocking của Laravel, cần Mockery.

    **Đã chạy thực tế:** 27 test đều lỗi ở setup, trước khi kiểm tra hành vi. Sửa bằng thêm dependency test cần thiết, cập nhật lockfile và chạy lại suite. Các probe riêng tắt console mocking để kiểm tra được hành vi mà không sửa dependency/source ứng dụng.

13. **[P2] API cập nhật dữ liệu trước khi từ chối request.**

    [UserController:64](C:/Users/MrTri/Documents/tkpro/api/app/Http/Controllers/Api/V1/Admin/UserController.php:64) gọi `$user->update($data)` trước khi kiểm tra việc tự gỡ role Super Admin và trả 422 ở dòng 72.

    **Đã tái hiện:** gửi tên mới kèm `roles=[]` cho tài khoản Super Admin của mình; API trả 422 nhưng tên trong DB vẫn đổi. Mật khẩu/email có thể bị cập nhật tương tự. Sửa bằng kiểm tra toàn bộ điều kiện trước mutation và dùng transaction cho cập nhật user/role.

14. **[P2, khi triển khai domain thật] Các biến domain bị hard-code trong Compose.**

    [docker-compose.yml](C:/Users/MrTri/Documents/tkpro/docker-compose.yml:79) đặt `SESSION_DOMAIN=localhost`, `FRONTEND_URL=http://localhost`, `APP_URL` và các biến liên quan cũng cố định. Compose không khai báo `env_file` cho `api/.env`/`cms/.env`; environment trong Compose sẽ ghi đè giá trị cùng tên trong file `.env` Laravel nếu file được copy vào image.

    Nếu chỉ sửa các `.env` theo một số bước trong tài liệu rồi deploy domain thật, cookie CMS vẫn đặt domain localhost và không duy trì được phiên đăng nhập; sitemap vẫn dùng localhost. CORS cũng sai nếu frontend/API dùng origin khác nhau. Sửa bằng parameterize các biến qua root `.env` hoặc override Compose cho production, đồng bộ URL public, cookie domain và origin. Localhost mặc định vẫn phù hợp cho dev.

Kiểm thử hồi quy sau khi sửa (thay thế các script tái hiện lỗi ban đầu):

```powershell
# Từ frontend/; cần npm ci.
npm test

# Từ api/; chỉ dùng SQLite trong bộ nhớ theo phpunit.xml.
# Tại máy đã bật pdo_sqlite thì bỏ tùy chọn -d.
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit --do-not-cache-result
```

[Kết quả chạy ban đầu](C:/Users/MrTri/Documents/tkpro/docs/review-api-test-output.txt) là dữ liệu lịch sử trước khi sửa. Kết quả xác minh hiện tại và hướng dẫn nâng cấp nằm trong `FIXES.md`.

Cần kiểm tra thêm stack Docker/MySQL và domain HTTPS thực tế khi Docker daemon sẵn sàng; môi trường rà soát hiện tại chưa chạy Docker Desktop.

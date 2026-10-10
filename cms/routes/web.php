<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TKPRO CMS — toàn bộ route nằm dưới prefix /admin
| Local: http://localhost:8001/admin | Qua nginx: http://localhost/admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // --- Khách (chưa đăng nhập) ---
    Route::middleware('guest')->group(function () {
        Route::get('/dang-nhap', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/dang-nhap', [LoginController::class, 'login'])->middleware('throttle:10,1');

        Route::get('/quen-mat-khau', [LoginController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/quen-mat-khau', [LoginController::class, 'sendResetLinkEmail'])
            ->name('password.email')->middleware('throttle:5,1');

        Route::get('/dat-lai-mat-khau/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
        Route::post('/dat-lai-mat-khau', [LoginController::class, 'reset'])->name('password.update');
    });

    // --- Đăng xuất ---
    Route::post('/dang-xuat', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

    // --- Khu vực quản trị (đăng nhập + ép đổi mật khẩu nếu cần) ---
    Route::middleware(['auth', 'must.change.password'])->group(function () {

        Route::get('/doi-mat-khau', [LoginController::class, 'showChangeForm'])->name('password.change');
        Route::put('/doi-mat-khau', [LoginController::class, 'updatePassword'])->name('password.change.update');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Sản phẩm
        Route::post('san-pham/{product}/trang-thai', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('san-pham/{product}/noi-bat', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
        Route::resource('san-pham', ProductController::class)
            ->parameters(['san-pham' => 'product'])->names('products')->except(['show']);

        // Danh mục sản phẩm (nhiều cấp)
        Route::resource('danh-muc-san-pham', ProductCategoryController::class)
            ->parameters(['danh-muc-san-pham' => 'productCategory'])->names('product_categories')->except(['show']);

        // Bài viết
        Route::post('bai-viet/{post}/trang-thai', [PostController::class, 'toggleStatus'])->name('posts.toggle-status');
        Route::resource('bai-viet', PostController::class)
            ->parameters(['bai-viet' => 'post'])->names('posts')->except(['show']);

        // Chuyên mục bài viết
        Route::resource('chuyen-muc', PostCategoryController::class)
            ->parameters(['chuyen-muc' => 'postCategory'])->names('post_categories')->except(['show']);

        // Trang tĩnh
        Route::resource('trang', PageController::class)
            ->parameters(['trang' => 'page'])->names('pages')->except(['show']);

        // Banner
        Route::post('banner/{banner}/kich-hoat', [BannerController::class, 'toggleActive'])->name('banners.toggle-active');
        Route::resource('banner', BannerController::class)
            ->parameters(['banner' => 'banner'])->names('banners')->except(['show']);

        // Menu + mục menu lồng nhau
        Route::resource('menu', MenuController::class)
            ->parameters(['menu' => 'menu'])->names('menus')->except(['show']);
        Route::resource('menu/{menu}/muc', MenuItemController::class)
            ->parameters(['menu' => 'menu', 'muc' => 'menuItem'])->names('menu_items')->except(['show']);

        // Thư viện ảnh
        Route::get('thu-vien-media', [MediaController::class, 'index'])->name('media.index');
        Route::post('thu-vien-media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('thu-vien-media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        // Cài đặt website
        Route::get('cai-dat', [SettingController::class, 'index'])->name('settings.index');
        Route::put('cai-dat', [SettingController::class, 'update'])->name('settings.update');

        // Người dùng & vai trò
        Route::resource('nguoi-dung', UserController::class)
            ->parameters(['nguoi-dung' => 'user'])->names('users')->except(['show']);
        Route::resource('vai-tro', RoleController::class)
            ->parameters(['vai-tro' => 'role'])->names('roles')->except(['show']);

        // Hộp thư liên hệ
        Route::get('tin-nhan-lien-he', [ContactMessageController::class, 'index'])->name('contact_messages.index');
        Route::get('tin-nhan-lien-he/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact_messages.show');
        Route::post('tin-nhan-lien-he/{contactMessage}/chua-doc', [ContactMessageController::class, 'markUnread'])->name('contact_messages.mark-unread');
        Route::delete('tin-nhan-lien-he/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact_messages.destroy');
    });
});

// Trang gốc của CMS: chuyển về /admin
Route::get('/', function () {
    return redirect('/admin');
});

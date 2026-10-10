<?php

use App\Http\Controllers\Api\V1\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\V1\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\V1\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\V1\Admin\PageController as AdminPageController;
use App\Http\Controllers\Api\V1\Admin\PostCategoryController as AdminPostCategoryController;
use App\Http\Controllers\Api\V1\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\V1\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ContactMessageController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostCategoryController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TKPRO (prefix /api/v1)
|--------------------------------------------------------------------------
| Public: khong can auth. Admin: auth:sanctum + middleware permission
| dang {module}.{action} (Super Admin bo qua moi kiem tra).
*/

Route::prefix('v1')->group(function () {

    // ---------------- Public ----------------
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('categories', [ProductCategoryController::class, 'index']);
    Route::get('categories/{slug}', [ProductCategoryController::class, 'show']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('post-categories', [PostCategoryController::class, 'index']);
    Route::get('pages/{slug}', [PageController::class, 'show']);
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('menus/{location}', [MenuController::class, 'show']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('search', [SearchController::class, 'index']);
    Route::post('contact-messages', [ContactMessageController::class, 'store'])
        ->middleware('throttle:contact');

    // ---------------- Auth (Sanctum bearer) ----------------
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/password', [AuthController::class, 'changePassword'])->middleware('throttle:5,1');

        // ---------------- Admin ----------------
        Route::prefix('admin')->middleware('must.change.password')->group(function () {

            // San pham
            Route::get('products', [AdminProductController::class, 'index'])->middleware('permission:products.view');
            Route::post('products', [AdminProductController::class, 'store'])->middleware('permission:products.create');
            Route::get('products/{product}', [AdminProductController::class, 'show'])->middleware('permission:products.view');
            Route::put('products/{product}', [AdminProductController::class, 'update'])->middleware('permission:products.update');
            Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:products.delete');

            // Danh muc san pham
            Route::get('product-categories', [AdminProductCategoryController::class, 'index'])->middleware('permission:product_categories.view');
            Route::post('product-categories', [AdminProductCategoryController::class, 'store'])->middleware('permission:product_categories.create');
            Route::get('product-categories/{category}', [AdminProductCategoryController::class, 'show'])->middleware('permission:product_categories.view');
            Route::put('product-categories/{category}', [AdminProductCategoryController::class, 'update'])->middleware('permission:product_categories.update');
            Route::delete('product-categories/{category}', [AdminProductCategoryController::class, 'destroy'])->middleware('permission:product_categories.delete');

            // Bai viet
            Route::get('posts', [AdminPostController::class, 'index'])->middleware('permission:posts.view');
            Route::post('posts', [AdminPostController::class, 'store'])->middleware('permission:posts.create');
            Route::get('posts/{post}', [AdminPostController::class, 'show'])->middleware('permission:posts.view');
            Route::put('posts/{post}', [AdminPostController::class, 'update'])->middleware('permission:posts.update');
            Route::delete('posts/{post}', [AdminPostController::class, 'destroy'])->middleware('permission:posts.delete');

            // Danh muc bai viet
            Route::get('post-categories', [AdminPostCategoryController::class, 'index'])->middleware('permission:post_categories.view');
            Route::post('post-categories', [AdminPostCategoryController::class, 'store'])->middleware('permission:post_categories.create');
            Route::get('post-categories/{category}', [AdminPostCategoryController::class, 'show'])->middleware('permission:post_categories.view');
            Route::put('post-categories/{category}', [AdminPostCategoryController::class, 'update'])->middleware('permission:post_categories.update');
            Route::delete('post-categories/{category}', [AdminPostCategoryController::class, 'destroy'])->middleware('permission:post_categories.delete');

            // Trang
            Route::get('pages', [AdminPageController::class, 'index'])->middleware('permission:pages.view');
            Route::post('pages', [AdminPageController::class, 'store'])->middleware('permission:pages.create');
            Route::get('pages/{page}', [AdminPageController::class, 'show'])->middleware('permission:pages.view');
            Route::put('pages/{page}', [AdminPageController::class, 'update'])->middleware('permission:pages.update');
            Route::delete('pages/{page}', [AdminPageController::class, 'destroy'])->middleware('permission:pages.delete');

            // Banner
            Route::get('banners', [AdminBannerController::class, 'index'])->middleware('permission:banners.view');
            Route::post('banners', [AdminBannerController::class, 'store'])->middleware('permission:banners.create');
            Route::get('banners/{banner}', [AdminBannerController::class, 'show'])->middleware('permission:banners.view');
            Route::put('banners/{banner}', [AdminBannerController::class, 'update'])->middleware('permission:banners.update');
            Route::delete('banners/{banner}', [AdminBannerController::class, 'destroy'])->middleware('permission:banners.delete');

            // Menu va muc menu
            Route::get('menus', [AdminMenuController::class, 'index'])->middleware('permission:menus.view');
            Route::post('menus', [AdminMenuController::class, 'store'])->middleware('permission:menus.create');
            Route::get('menus/{menu}', [AdminMenuController::class, 'show'])->middleware('permission:menus.view');
            Route::put('menus/{menu}', [AdminMenuController::class, 'update'])->middleware('permission:menus.update');
            Route::delete('menus/{menu}', [AdminMenuController::class, 'destroy'])->middleware('permission:menus.delete');
            Route::get('menus/{menu}/items', [AdminMenuController::class, 'items'])->middleware('permission:menus.view');
            Route::post('menus/{menu}/items', [AdminMenuController::class, 'storeItem'])->middleware('permission:menus.create');
            Route::put('menu-items/{item}', [AdminMenuController::class, 'updateItem'])->middleware('permission:menus.update');
            Route::delete('menu-items/{item}', [AdminMenuController::class, 'destroyItem'])->middleware('permission:menus.delete');

            // Media
            Route::get('media', [AdminMediaController::class, 'index'])->middleware('permission:media.view');
            Route::post('media/upload', [AdminMediaController::class, 'upload'])->middleware('permission:media.create');
            Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->middleware('permission:media.delete');

            // Cai dat
            Route::get('settings', [AdminSettingController::class, 'index'])->middleware('permission:settings.view');
            Route::put('settings', [AdminSettingController::class, 'update'])->middleware('permission:settings.update');

            // Nguoi dung
            Route::get('users', [AdminUserController::class, 'index'])->middleware('permission:users.view');
            Route::post('users', [AdminUserController::class, 'store'])->middleware('permission:users.create');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->middleware('permission:users.view');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->middleware('permission:users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->middleware('permission:users.delete');

            // Vai tro va quyen
            Route::get('roles/permissions', [AdminRoleController::class, 'permissions'])->middleware('permission:roles.view');
            Route::get('roles', [AdminRoleController::class, 'index'])->middleware('permission:roles.view');
            Route::post('roles', [AdminRoleController::class, 'store'])->middleware('permission:roles.create');
            Route::get('roles/{role}', [AdminRoleController::class, 'show'])->middleware('permission:roles.view');
            Route::put('roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:roles.update');
            Route::delete('roles/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:roles.delete');

            // Tin nhan lien he
            Route::get('contact-messages', [AdminContactMessageController::class, 'index'])->middleware('permission:contact_messages.view');
            Route::put('contact-messages/{message}', [AdminContactMessageController::class, 'update'])->middleware('permission:contact_messages.update');
            Route::delete('contact-messages/{message}', [AdminContactMessageController::class, 'destroy'])->middleware('permission:contact_messages.delete');
        });
    });
});

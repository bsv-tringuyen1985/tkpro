<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Tao vai tro va quyen theo contract:
 * {products,product_categories,posts,post_categories,pages,banners,menus,media,
 *  settings,users,roles,contact_messages} x {view,create,update,delete,publish}.
 */
class RolesPermissionsSeeder extends Seeder
{
    public const MODULES = [
        'products' => 'Sản phẩm',
        'product_categories' => 'Danh mục sản phẩm',
        'posts' => 'Bài viết',
        'post_categories' => 'Danh mục bài viết',
        'pages' => 'Trang nội dung',
        'banners' => 'Banner',
        'menus' => 'Menu',
        'media' => 'Thư viện media',
        'settings' => 'Cài đặt',
        'users' => 'Người dùng',
        'roles' => 'Vai trò & phân quyền',
        'contact_messages' => 'Tin nhắn liên hệ',
    ];

    public const ACTIONS = [
        'view' => 'Xem',
        'create' => 'Tạo',
        'update' => 'Sửa',
        'delete' => 'Xóa',
        'publish' => 'Xuất bản',
    ];

    public function run(): void
    {
        // 1. Tao permissions.
        foreach (self::MODULES as $module => $moduleLabel) {
            foreach (self::ACTIONS as $action => $actionLabel) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}"],
                    ['display_name' => "{$actionLabel} {$moduleLabel}", 'group' => $module]
                );
            }
        }

        // 2. Tao roles.
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => 'Super Admin']
        );
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin']
        );
        $editor = Role::firstOrCreate(
            ['name' => 'editor'],
            ['display_name' => 'Editor']
        );

        // 3. Gan quyen.
        $allIds = Permission::pluck('id');

        // Super Admin: tat ca.
        $superAdmin->permissions()->sync($allIds);

        // Admin: tat ca tru quan ly vai tro (chi Super Admin duoc dong den).
        $adminIds = Permission::where('group', '!=', 'roles')->pluck('id');
        $admin->permissions()->sync($adminIds);

        // Editor: chi noi dung, khong duoc xoa san pham/bai viet, khong dong den
        // settings, users, roles, chi xem tin nhan lien he.
        $editorPermissions = [
            'products.view', 'products.create', 'products.update', 'products.publish',
            'product_categories.view', 'product_categories.create', 'product_categories.update', 'product_categories.publish',
            'posts.view', 'posts.create', 'posts.update', 'posts.publish',
            'post_categories.view', 'post_categories.create', 'post_categories.update', 'post_categories.publish',
            'pages.view', 'pages.create', 'pages.update', 'pages.publish',
            'banners.view', 'banners.create', 'banners.update',
            'menus.view', 'menus.create', 'menus.update',
            'media.view', 'media.create', 'media.update',
            'contact_messages.view',
        ];
        $editorIds = Permission::whereIn('name', $editorPermissions)->pluck('id');
        $editor->permissions()->sync($editorIds);
    }
}

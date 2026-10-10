<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Lấy giá trị cài đặt website theo key (có cache trong request).
     */
    function setting(string $key, $default = null)
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key] ?? $default;
        }

        try {
            $value = Setting::where('key', $key)->value('value');
        } catch (\Throwable $e) {
            $value = null;
        }

        $cache[$key] = $value;

        return $value ?? $default;
    }
}

if (! function_exists('has_permission')) {
    /**
     * Kiểm tra user đang đăng nhập có quyền hay không.
     */
    function has_permission(string $permission): bool
    {
        $user = auth()->user();

        return $user ? $user->hasPermission($permission) : false;
    }
}

if (! function_exists('format_price')) {
    /**
     * Định dạng giá VNĐ. Null = "Liên hệ".
     */
    function format_price($price): string
    {
        if ($price === null || $price === '') {
            return 'Liên hệ';
        }

        return number_format((int) $price, 0, ',', '.').'₫';
    }
}

if (! function_exists('module_label')) {
    /**
     * Tên tiếng Việt của nhóm module (dùng cho trang phân quyền).
     */
    function module_label(string $group): string
    {
        return [
            'products' => 'Sản phẩm',
            'product_categories' => 'Danh mục sản phẩm',
            'posts' => 'Bài viết',
            'post_categories' => 'Chuyên mục bài viết',
            'pages' => 'Trang tĩnh',
            'banners' => 'Banner',
            'menus' => 'Menu',
            'media' => 'Thư viện ảnh',
            'settings' => 'Cài đặt',
            'users' => 'Người dùng',
            'roles' => 'Vai trò & quyền',
            'contact_messages' => 'Tin nhắn liên hệ',
        ][$group] ?? $group;
    }
}

if (! function_exists('permission_action_label')) {
    /**
     * Tên tiếng Việt của hành động trong quyền.
     */
    function permission_action_label(string $action): string
    {
        return [
            'view' => 'Xem',
            'create' => 'Thêm mới',
            'update' => 'Chỉnh sửa',
            'delete' => 'Xóa',
            'publish' => 'Xuất bản / Duyệt',
        ][$action] ?? $action;
    }
}

if (! function_exists('status_label')) {
    /**
     * Nhãn trạng thái draft/published.
     */
    function status_label(string $status): string
    {
        return $status === 'published' ? 'Đã xuất bản' : 'Bản nháp';
    }
}

if (! function_exists('storage_url')) {
    /**
     * Chuyển path trong storage thành URL public dạng /storage/<path>.
     */
    function storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.$path;
    }
}

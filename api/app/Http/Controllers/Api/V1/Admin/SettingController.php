<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsBulkRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    /** Tất cả cài đặt (kể cả nhóm private) — chỉ admin. */
    public function index(): JsonResponse
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();

        $grouped = [];
        foreach ($settings as $setting) {
            $grouped[$setting->group][$setting->key] = $setting->value;
        }

        return $this->success($grouped);
    }

    /** Lưu hàng loạt cài đặt dạng key => value. */
    public function update(SettingsBulkRequest $request): JsonResponse
    {
        $group = $request->input('group', 'general');

        foreach ($request->input('settings', []) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        return $this->success(Setting::publicAll(), 'Lưu cài đặt thành công.');
    }
}

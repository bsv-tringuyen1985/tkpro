<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index']);
        $this->middleware('permission:settings.update')->only(['update']);
    }

    /**
     * Định nghĩa các trường cài đặt theo nhóm.
     */
    public static function fields(): array
    {
        return [
            'general' => [
                'label' => 'Chung',
                'fields' => [
                    'site_name' => ['label' => 'Tên website', 'type' => 'text', 'required' => true],
                    'logo' => ['label' => 'Logo', 'type' => 'image'],
                    'favicon' => ['label' => 'Favicon', 'type' => 'image'],
                    'announcement' => ['label' => 'Thông báo chạy trên đầu trang', 'type' => 'text'],
                    'copyright' => ['label' => 'Dòng bản quyền (footer)', 'type' => 'text'],
                    'footer_about' => ['label' => 'Giới thiệu ngắn (footer)', 'type' => 'textarea'],
                ],
            ],
            'contact' => [
                'label' => 'Liên hệ',
                'fields' => [
                    'hotline' => ['label' => 'Hotline', 'type' => 'text'],
                    'email' => ['label' => 'Email', 'type' => 'email'],
                    'address' => ['label' => 'Địa chỉ', 'type' => 'text'],
                    'working_hours' => ['label' => 'Giờ làm việc', 'type' => 'text'],
                    'google_maps_embed' => ['label' => 'Mã nhúng Google Maps (iframe)', 'type' => 'textarea'],
                ],
            ],
            'social' => [
                'label' => 'Mạng xã hội',
                'fields' => [
                    'facebook' => ['label' => 'Facebook URL', 'type' => 'url'],
                    'youtube' => ['label' => 'Youtube URL', 'type' => 'url'],
                    'zalo' => ['label' => 'Zalo URL / số điện thoại', 'type' => 'text'],
                ],
            ],
        ];
    }

    public function index()
    {
        $groups = self::fields();
        $values = Setting::pluck('value', 'key')->toArray();
        foreach (['facebook', 'youtube'] as $key) {
            if (($values[$key] ?? null) === '#') { $values[$key] = ''; }
        }

        return view('settings.index', compact('groups', 'values'));
    }

    public function update(Request $request)
    {
        $groups = self::fields();
        foreach (['facebook', 'youtube'] as $key) {
            if (trim((string) $request->input($key)) === '#') { $request->merge([$key => null]); }
        }

        $rules = [];
        $messages = [];
        foreach ($groups as $groupKey => $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] === 'image') {
                    $rules[$key.'_file'] = MediaService::imageRules(false);
                    $messages[$key.'_file.mimes'] = $field['label'].' phải là ảnh jpg, jpeg, png, webp hoặc svg.';
                    $messages[$key.'_file.max'] = $field['label'].' không được quá 5MB.';
                    continue;
                }

                $rule = 'nullable|string|max:2000';
                if (! empty($field['required'])) {
                    $rule = 'required|string|max:255';
                    $messages[$key.'.required'] = 'Vui lòng nhập "'.$field['label'].'".';
                }
                if ($field['type'] === 'email') {
                    $rule .= '|email';
                    $messages[$key.'.email'] = '"'.$field['label'].'" không đúng định dạng email.';
                }
                if ($field['type'] === 'url') {
                    $rule .= '|url';
                    $messages[$key.'.url'] = '"'.$field['label'].'" phải là đường dẫn URL hợp lệ (bắt đầu bằng http).';
                }
                $rules[$key] = $rule;
            }
        }

        $data = $request->validate($rules, $messages);

        foreach ($groups as $groupKey => $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] === 'image') {
                    if ($request->hasFile($key.'_file')) {
                        $old = Setting::get($key);
                        if ($old && ! str_starts_with($old, 'http')) {
                            Storage::disk('public')->delete($old);
                        }
                        $media = MediaService::storeUpload($request->file($key.'_file'), 'settings');
                        Setting::set($key, $media->url, $groupKey);
                    } elseif ($request->has($key.'_remove')) {
                        $old = Setting::get($key);
                        if ($old && ! str_starts_with($old, 'http')) {
                            Storage::disk('public')->delete($old);
                        }
                        Setting::set($key, null, $groupKey);
                    }
                    continue;
                }

                Setting::set($key, $data[$key] ?? null, $groupKey);
            }
        }

        return back()->with('success', 'Đã lưu cài đặt website.');
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class SettingsBulkRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
            'group' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Vui lòng gửi danh sách cài đặt cần lưu.',
            'settings.array' => 'Danh sách cài đặt không đúng định dạng.',
        ];
    }
}

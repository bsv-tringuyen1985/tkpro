<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class StoreRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập mã vai trò.',
            'name.alpha_dash' => 'Mã vai trò chỉ gồm chữ, số, gạch ngang và gạch dưới.',
            'name.unique' => 'Mã vai trò đã được sử dụng.',
            'display_name.required' => 'Vui lòng nhập tên hiển thị.',
            'permissions.*.exists' => 'Quyền không tồn tại.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class MediaUploadRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = (int) env('MEDIA_MAX_SIZE_KB', 5120);

        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:'.$maxKb],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file để tải lên.',
            'file.mimes' => 'Chỉ chấp nhận file ảnh: jpg, jpeg, png, webp, svg.',
            'file.max' => 'Dung lượng file tối đa :max KB.',
        ];
    }
}

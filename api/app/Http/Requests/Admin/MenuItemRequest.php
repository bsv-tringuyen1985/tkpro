<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class MenuItemRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:url,page'],
            'url' => ['nullable', 'string', 'max:500', 'required_if:type,url'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id', 'required_if:type,page'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id', 'not_in:'.$itemId],
            'target' => ['nullable', 'in:_self,_blank'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề mục menu.',
            'url.required_if' => 'Vui lòng nhập đường dẫn cho mục menu kiểu URL.',
            'page_id.required_if' => 'Vui lòng chọn trang cho mục menu kiểu trang.',
            'parent_id.not_in' => 'Mục menu không thể là cha của chính nó.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\HasSeoRules;

class StorePostRequest extends ApiFormRequest
{
    use HasSeoRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRule('posts'),
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ], $this->seoRules());
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'slug.regex' => 'Slug chỉ gồm chữ thường, số và dấu gạch ngang.',
            'slug.unique' => 'Slug đã được sử dụng.',
            'category_id.exists' => 'Danh mục bài viết không tồn tại.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}

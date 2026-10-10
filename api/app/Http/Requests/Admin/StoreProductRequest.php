<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\HasSeoRules;

class StoreProductRequest extends ApiFormRequest
{
    use HasSeoRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRule('products'),
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'technical_specifications' => ['nullable', 'array'],
            'technical_specifications.*.label' => ['required_with:technical_specifications', 'string', 'max:255'],
            'technical_specifications.*.value' => ['required_with:technical_specifications', 'string', 'max:1000'],
            'price' => ['nullable', 'integer', 'min:0'],
            'price_label' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            // Ảnh sản phẩm: gắn từ các file đã upload ở thư viện media.
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ], $this->seoRules());
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'category_id.exists' => 'Danh mục sản phẩm không tồn tại.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'slug.regex' => 'Slug chỉ gồm chữ thường, số và dấu gạch ngang.',
            'slug.unique' => 'Slug đã được sử dụng.',
            'sku.unique' => 'SKU đã được sử dụng.',
            'price.integer' => 'Giá phải là số nguyên (VNĐ).',
            'status.in' => 'Trạng thái không hợp lệ.',
            'media_ids.*.exists' => 'File media không tồn tại.',
        ];
    }
}

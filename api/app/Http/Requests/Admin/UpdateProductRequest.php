<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\HasSeoRules;

class UpdateProductRequest extends ApiFormRequest
{
    use HasSeoRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return array_merge([
            'category_id' => ['sometimes', 'required', 'integer', 'exists:product_categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => $this->slugRule('products', $id),
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,'.$id],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'technical_specifications' => ['nullable', 'array'],
            'technical_specifications.*.label' => ['required_with:technical_specifications', 'string', 'max:255'],
            'technical_specifications.*.value' => ['required_with:technical_specifications', 'string', 'max:1000'],
            'price' => ['nullable', 'integer', 'min:0'],
            'price_label' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ], $this->seoRules());
    }
}

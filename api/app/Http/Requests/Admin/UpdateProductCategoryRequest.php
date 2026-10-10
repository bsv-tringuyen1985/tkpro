<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\HasSeoRules;

class UpdateProductCategoryRequest extends ApiFormRequest
{
    use HasSeoRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return array_merge([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => $this->slugRule('product_categories', $id),
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id', 'not_in:'.$id],
            'status' => ['sometimes', 'required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], $this->seoRules());
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'Danh mục không thể là cha của chính nó.',
        ];
    }
}

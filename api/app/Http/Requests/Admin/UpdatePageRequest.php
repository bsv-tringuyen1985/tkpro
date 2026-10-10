<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\HasSeoRules;

class UpdatePageRequest extends ApiFormRequest
{
    use HasSeoRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('page')?->id;

        return array_merge([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => $this->slugRule('pages', $id),
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], $this->seoRules());
    }
}

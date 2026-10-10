<?php

namespace App\Http\Requests\Concerns;

/** Các rule dùng chung cho khối SEO (seo_meta). */
trait HasSeoRules
{
    protected function seoRules(): array
    {
        return [
            'seo' => ['nullable', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.meta_keywords' => ['nullable', 'string', 'max:255'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'string', 'max:500'],
            'seo.robots' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function slugRule(string $table, ?int $ignoreId = null): array
    {
        $rule = 'unique:'.$table.',slug';
        if ($ignoreId) {
            $rule .= ','.$ignoreId;
        }

        return ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rule];
    }
}

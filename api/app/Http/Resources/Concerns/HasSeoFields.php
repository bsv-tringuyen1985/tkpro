<?php

namespace App\Http\Resources\Concerns;

trait HasSeoFields
{
    protected function seoFields(): array
    {
        $relation = $this->resource->relationLoaded('seoMeta') ? $this->resource->getRelation('seoMeta') : null;
        $seo = $relation ? $relation->only([
            'meta_title', 'meta_description', 'meta_keywords', 'og_title',
            'og_description', 'og_image', 'canonical_url', 'robots',
        ]) : [];
        foreach (['meta_title', 'meta_description', 'meta_keywords'] as $key) {
            if (array_key_exists($key, $this->resource->getAttributes())) {
                $seo[$key] = $this->resource->getAttribute($key);
            }
        }
        return [
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
            'meta_keywords' => $seo['meta_keywords'] ?? null,
            'seo' => $seo,
        ];
    }
}

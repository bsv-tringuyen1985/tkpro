<?php

namespace App\Http\Controllers\Api\V1\Admin\Concerns;

/** Lưu khối SEO (seo_meta) dạng morphOne cho Product/Post/Page/ProductCategory. */
trait SavesSeoMeta
{
    protected function saveSeoMeta(mixed $model, mixed $seo): void
    {
        if (! is_array($seo)) {
            return;
        }

        if ($seo === []) {
            return;
        }

        $columns = [];
        foreach (['meta_title', 'meta_description', 'meta_keywords'] as $key) {
            if (array_key_exists($key, $seo) && \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), $key)) {
                $columns[$key] = $seo[$key];
            }
        }
        if ($columns !== []) {
            $model->update($columns);
        }
        $model->seoMeta()->updateOrCreate([], $seo);
        $model->refresh()->load('seoMeta');
    }
}

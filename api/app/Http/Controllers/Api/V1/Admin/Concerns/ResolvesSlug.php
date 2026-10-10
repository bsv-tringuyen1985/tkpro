<?php

namespace App\Http\Controllers\Api\V1\Admin\Concerns;

use App\Helpers\SlugHelper;

/** Xử lý slug: tự sinh từ name/title, đảm bảo duy nhất, cho phép sửa tay. */
trait ResolvesSlug
{
    protected function resolveSlug(string $modelClass, array $data, mixed $model = null, string $nameKey = 'name'): array
    {
        $ignoreId = $model?->id;
        $slugInput = $data['slug'] ?? null;
        unset($data['slug']);

        $name = $data[$nameKey] ?? null;
        if (is_string($slugInput) && $slugInput !== '') {
            $data['slug'] = SlugHelper::unique($modelClass, $slugInput, $ignoreId);
        } elseif (is_string($name) && $name !== '' && (! $model || $name !== $model->{$nameKey})) {
            $data['slug'] = SlugHelper::unique($modelClass, SlugHelper::make($name), $ignoreId);
        }

        return $data;
    }
}

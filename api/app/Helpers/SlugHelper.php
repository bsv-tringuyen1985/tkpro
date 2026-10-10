<?php

namespace App\Helpers;

/**
 * Sinh slug tieng Viet khong dau, theo dung quy uoc contract 3.3:
 * lowercase, khong dau (d->d), noi bang gach ngang, duy nhat (them -2 khi trung).
 */
class SlugHelper
{
    public static function make(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        // Bang chuyen doi chi tiet tung ky tu co dau sang khong dau.
        $chars = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];
        $value = strtr($value, $chars);

        // Moi cum ky tu khac a-z 0-9 thanh mot gach ngang.
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);

        return trim((string) $value, '-');
    }

    /**
     * Dam bao slug duy nhat trong bang cua model (them -2, -3... khi trung).
     *
     * @param class-string $modelClass
     */
    public static function unique(string $modelClass, string $base, ?int $exceptId = null): string
    {
        $base = $base !== '' ? $base : 'item';
        $slug = $base;
        $i = 2;

        while (
            $modelClass::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    /** Cac nhom setting duoc phep tra ve cho frontend public. */
    public const PUBLIC_GROUPS = ['general', 'contact', 'social', 'seo'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function publicAll(): array
    {
        return static::whereIn('group', self::PUBLIC_GROUPS)
            ->orderBy('key')
            ->pluck('value', 'key')
            ->all();
    }
}

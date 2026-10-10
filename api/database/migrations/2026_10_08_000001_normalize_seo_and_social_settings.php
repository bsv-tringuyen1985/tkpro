<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->whereIn('key', ['facebook', 'youtube'])->where('value', '#')->update(['value' => null]);
        $entities = [
            'App\\Models\\Product' => ['products', ['meta_title', 'meta_description', 'meta_keywords']],
            'App\\Models\\Post' => ['posts', ['meta_title', 'meta_description', 'meta_keywords']],
            'App\\Models\\Page' => ['pages', ['meta_title', 'meta_description']],
            'App\\Models\\ProductCategory' => ['product_categories', ['meta_title', 'meta_description']],
        ];
        foreach ($entities as $type => [$table, $fields]) {
            DB::table('seo_meta')->where('metable_type', $type)->orderBy('id')->chunkById(100, function ($rows) use ($table, $fields) {
                foreach ($rows as $row) {
                    foreach ($fields as $field) {
                        if ($row->$field !== null) {
                            DB::table($table)->where('id', $row->metable_id)->whereNull($field)->update([$field => $row->$field]);
                        }
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Data normalization is intentionally preserved on rollback.
    }
};

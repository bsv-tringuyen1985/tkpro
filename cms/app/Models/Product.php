<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'technical_specifications',
        'price',
        'price_label',
        'thumbnail',
        'status',
        'featured',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'integer',
        'featured' => 'boolean',
        'sort_order' => 'integer',
        'technical_specifications' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'metable');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return storage_url($this->thumbnail);
        }

        $first = $this->images->first();

        return $first ? $first->url : null;
    }
}

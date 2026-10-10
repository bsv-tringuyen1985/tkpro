<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'type', 'url',
        'page_id', 'target', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * Dung cay menu tu danh sach phang (da orderBy sort_order).
     * Dung setRelation de Resource co the dung whenLoaded('children').
     */
    public static function buildTree(Collection $items, $parentId = null): Collection
    {
        return $items->where('parent_id', $parentId)->values()->map(function (MenuItem $item) use ($items) {
            $item->setRelation('children', self::buildTree($items, $item->id));

            return $item;
        });
    }

    /** URL cuoi cung: item kieu page thi tro toi slug cua page. */
    public function resolvedUrl(): ?string
    {
        if ($this->type === 'page' && $this->page) {
            return $this->page->slug === 'trang-chu' ? '/' : '/'.$this->page->slug;
        }

        return $this->url;
    }
}

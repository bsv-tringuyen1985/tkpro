<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'url',
        'page_id',
        'target',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * URL hiển thị của mục menu.
     */
    public function resolvedUrl(): string
    {
        if ($this->type === 'page' && $this->page) {
            return '/'.$this->page->slug;
        }

        return $this->url ?: '#';
    }
}

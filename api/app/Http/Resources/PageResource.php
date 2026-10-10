<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    use \App\Http\Resources\Concerns\HasSeoFields;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            ...$this->seoFields(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

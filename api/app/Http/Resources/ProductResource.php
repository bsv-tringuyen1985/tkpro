<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use \App\Http\Resources\Concerns\HasSeoFields;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'technical_specifications' => $this->technical_specifications,
            'price' => $this->price,
            // Chuỗi giá hiển thị theo brand: "Giá tham khảo: ..." hoặc "Liên hệ".
            'price_display' => $this->price !== null
                ? 'Giá tham khảo: '.number_format($this->price, 0, ',', '.').'₫'
                : 'Liên hệ',
            'price_label' => $this->price_label,
            'thumbnail' => $this->thumbnail,
            'status' => $this->status,
            'featured' => $this->featured,
            'sort_order' => $this->sort_order,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            ...$this->seoFields(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

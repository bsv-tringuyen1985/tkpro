<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Danh sách sản phẩm đã xuất bản.
     * Lọc: q (tìm theo tên/SKU/mô tả), category (slug), featured=1,
     * sort (newest|price_asc|price_desc|name_asc), per_page (mặc định 12, tối đa 60).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::published()->with(['category', 'images']);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        if ($categorySlug = $request->string('category')->trim()->toString()) {
            $category = ProductCategory::where('slug', $categorySlug)->first();
            if ($category) {
                // Bao gồm cả sản phẩm của danh mục con.
                $ids = [$category->id];
                $ids = array_merge($ids, $category->children()->pluck('id')->all());
                $query->whereIn('category_id', $ids);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            'name_asc' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('sort_order')->orderByDesc('created_at'),
        };

        $perPage = min(max((int) $request->input('per_page', 12), 1), 60);
        $products = $query->paginate($perPage)->withQueryString();

        return $this->paginated(ProductResource::collection($products));
    }

    /** Chi tiết sản phẩm kèm danh mục, ảnh và sản phẩm liên quan. */
    public function show(string $slug): JsonResponse
    {
        $product = Product::published()
            ->with(['category', 'images', 'seoMeta'])
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return $this->notFound('Không tìm thấy sản phẩm.');
        }

        $related = Product::published()
            ->with(['images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return $this->success([
            'product' => new ProductResource($product),
            'related' => ProductResource::collection($related),
        ]);
    }
}

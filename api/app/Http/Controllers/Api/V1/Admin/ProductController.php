<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Api\V1\Admin\Concerns\SavesSeoMeta;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse, ResolvesSlug, SavesSeoMeta;

    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images']);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $query->orderBy('sort_order')->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $products = $query->paginate($perPage)->withQueryString();

        return $this->paginated(ProductResource::collection($products));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $this->resolveSlug(Product::class, $request->safe()->except(['media_ids', 'seo']));
        $product = Product::create($data);

        if ($request->has('media_ids')) {
            $this->syncImages($product, (array) $request->input('media_ids', []));
        }
        $this->saveSeoMeta($product, $request->input('seo'));

        $product->load(['category', 'images', 'seoMeta']);

        return $this->success(new ProductResource($product), 'Tạo sản phẩm thành công.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images', 'seoMeta']);

        return $this->success(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $this->resolveSlug(Product::class, $request->safe()->except(['media_ids', 'seo']), $product);
        $product->update($data);

        if ($request->has('media_ids')) {
            $this->syncImages($product, (array) $request->input('media_ids', []));
        }
        $this->saveSeoMeta($product, $request->input('seo'));

        $product->load(['category', 'images', 'seoMeta']);

        return $this->success(new ProductResource($product), 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success(null, 'Xóa sản phẩm thành công.');
    }

    /** Đồng bộ ảnh sản phẩm từ các file đã upload trong thư viện media. */
    protected function syncImages(Product $product, array $mediaIds): void
    {
        $product->images()->delete();

        foreach (array_values($mediaIds) as $i => $mediaId) {
            $media = Media::find($mediaId);
            if (! $media) {
                continue;
            }
            $product->images()->create([
                'path' => $media->path,
                'url' => $media->url,
                'filename' => $media->filename,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'alt' => $media->alt ?: $product->name,
                'sort_order' => $i,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use ApiResponse;

    /** Cây danh mục đã xuất bản kèm số lượng sản phẩm. */
    public function index(): JsonResponse
    {
        $categories = ProductCategory::published()
            ->topLevel()
            ->with(['seoMeta', 'children' => function ($q) {
                $q->published()->withCount(['products' => fn ($w) => $w->published()]);
            }])
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->success(ProductCategoryResource::collection($categories));
    }

    /** Chi tiết danh mục kèm danh mục con và sản phẩm thuộc danh mục (phân trang). */
    public function show(Request $request, string $slug): JsonResponse
    {
        $category = ProductCategory::published()
            ->with(['seoMeta', 'children' => function ($q) {
                $q->published()->withCount(['products' => fn ($w) => $w->published()]);
            }])
            ->withCount(['products' => fn ($q) => $q->published()])
            ->where('slug', $slug)
            ->first();

        if (! $category) {
            return $this->notFound('Không tìm thấy danh mục.');
        }

        $ids = array_merge([$category->id], $category->children->pluck('id')->all());

        $perPage = min(max((int) $request->input('per_page', 12), 1), 60);
        $products = Product::published()
            ->with(['images'])
            ->whereIn('category_id', $ids)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $productsArray = ProductResource::collection($products)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Thành công.',
            'data' => [
                'category' => new ProductCategoryResource($category),
                'products' => $productsArray['data'] ?? [],
            ],
            'meta' => [
                'current_page' => $productsArray['meta']['current_page'] ?? 1,
                'per_page' => $productsArray['meta']['per_page'] ?? 15,
                'total' => $productsArray['meta']['total'] ?? 0,
                'last_page' => $productsArray['meta']['last_page'] ?? 1,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Api\V1\Admin\Concerns\SavesSeoMeta;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use ApiResponse, ResolvesSlug, SavesSeoMeta;

    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::with(['parent'])
            ->withCount('products');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $query->orderBy('sort_order')->orderBy('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $categories = $query->paginate($perPage)->withQueryString();

        return $this->paginated(ProductCategoryResource::collection($categories));
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $data = $this->resolveSlug(ProductCategory::class, $request->safe()->except(['seo']));
        $category = ProductCategory::create($data);
        $this->saveSeoMeta($category, $request->input('seo'));

        return $this->success(new ProductCategoryResource($category), 'Tạo danh mục thành công.', 201);
    }

    public function show(ProductCategory $category): JsonResponse
    {
        $category->load(['parent', 'children', 'seoMeta']);

        return $this->success(new ProductCategoryResource($category));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): JsonResponse
    {
        $data = $this->resolveSlug(ProductCategory::class, $request->safe()->except(['seo']), $category);
        $category->update($data);
        $this->saveSeoMeta($category, $request->input('seo'));

        return $this->success(new ProductCategoryResource($category), 'Cập nhật danh mục thành công.');
    }

    public function destroy(ProductCategory $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return $this->error('Không thể xóa danh mục đang chứa sản phẩm.', 422);
        }
        if ($category->children()->exists()) {
            return $this->error('Không thể xóa danh mục đang có danh mục con.', 422);
        }

        $category->delete();

        return $this->success(null, 'Xóa danh mục thành công.');
    }
}

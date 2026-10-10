<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\SlugHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Http\Resources\PostCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = PostCategory::withCount('posts');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $query->orderBy('sort_order')->orderBy('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(PostCategoryResource::collection($query->paginate($perPage)->withQueryString()));
    }

    public function store(StorePostCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = SlugHelper::unique(PostCategory::class, $data['slug'] ?? SlugHelper::make($data['name']));
        $category = PostCategory::create($data);

        return $this->success(new PostCategoryResource($category), 'Tạo danh mục bài viết thành công.', 201);
    }

    public function show(PostCategory $category): JsonResponse
    {
        return $this->success(new PostCategoryResource($category->loadCount('posts')));
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $category): JsonResponse
    {
        $data = $request->validated();
        $slugInput = $data['slug'] ?? null;
        unset($data['slug']);
        if (is_string($slugInput) && $slugInput !== '') {
            $data['slug'] = SlugHelper::unique(PostCategory::class, $slugInput, $category->id);
        } elseif (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = SlugHelper::unique(PostCategory::class, SlugHelper::make($data['name']), $category->id);
        }

        $category->update($data);

        return $this->success(new PostCategoryResource($category), 'Cập nhật danh mục bài viết thành công.');
    }

    public function destroy(PostCategory $category): JsonResponse
    {
        // Bài viết thuộc danh mục bị xóa sẽ được set category_id = null (nullOnDelete).
        $category->delete();

        return $this->success(null, 'Xóa danh mục bài viết thành công.');
    }
}

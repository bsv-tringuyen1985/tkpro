<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Traits\ApiResponse;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse;

    /** Danh sách bài viết đã xuất bản. Lọc: q, category (slug), per_page. */
    public function index(Request $request): JsonResponse
    {
        $query = Post::published()->with(['category', 'author']);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        if ($categorySlug = $request->string('category')->trim()->toString()) {
            $category = PostCategory::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->orderByDesc('published_at')->orderByDesc('created_at');

        $perPage = min(max((int) $request->input('per_page', 10), 1), 60);
        $posts = $query->paginate($perPage)->withQueryString();

        return $this->paginated(PostResource::collection($posts));
    }

    /** Chi tiết bài viết kèm danh mục, tác giả và bài liên quan. */
    public function show(string $slug): JsonResponse
    {
        $post = Post::published()
            ->with(['category', 'author', 'seoMeta'])
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            return $this->notFound('Không tìm thấy bài viết.');
        }

        $related = Post::published()
            ->with(['category'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return $this->success([
            'post' => new PostResource($post),
            'related' => PostResource::collection($related),
        ]);
    }
}

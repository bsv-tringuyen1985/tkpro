<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Api\V1\Admin\Concerns\SavesSeoMeta;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Traits\ApiResponse;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse, ResolvesSlug, SavesSeoMeta;

    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'author']);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('title', 'like', "%{$q}%");
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $query->orderByDesc('published_at')->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $posts = $query->paginate($perPage)->withQueryString();

        return $this->paginated(PostResource::collection($posts));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $this->resolveSlug(Post::class, $request->safe()->except(['seo']), null, 'title');
        $data['user_id'] = $request->user()->id;
        if (($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);
        $this->saveSeoMeta($post, $request->input('seo'));
        $post->load(['category', 'author', 'seoMeta']);

        return $this->success(new PostResource($post), 'Tạo bài viết thành công.', 201);
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['category', 'author', 'seoMeta']);

        return $this->success(new PostResource($post));
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $data = $this->resolveSlug(Post::class, $request->safe()->except(['seo']), $post, 'title');
        if (($data['status'] ?? $post->status) === 'published' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post->update($data);
        $this->saveSeoMeta($post, $request->input('seo'));
        $post->load(['category', 'author', 'seoMeta']);

        return $this->success(new PostResource($post), 'Cập nhật bài viết thành công.');
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return $this->success(null, 'Xóa bài viết thành công.');
    }
}

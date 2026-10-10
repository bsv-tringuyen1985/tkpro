<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\MediaService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:posts.view')->only(['index', 'show']);
        $this->middleware('permission:posts.create')->only(['create', 'store']);
        $this->middleware('permission:posts.update')->only(['edit', 'update']);
        $this->middleware('permission:posts.delete')->only(['destroy']);
        $this->middleware('permission:posts.publish')->only(['toggleStatus']);
    }

    public function index(Request $request)
    {
        $query = Post::with(['category', 'author'])->orderByDesc('created_at');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%'.$q.'%')
                    ->orWhere('excerpt', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = PostCategory::orderBy('name')->get();

        return view('posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = PostCategory::orderBy('name')->pluck('name', 'id');

        return view('posts.form', ['post' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $post = new Post();
        $this->fillPost($post, $data, $request);
        $post->user_id = $request->user()->id;
        $post->save();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Đã đăng bài viết "'.$post->title.'".');
    }

    public function edit(Post $post)
    {
        $categories = PostCategory::orderBy('name')->pluck('name', 'id');

        return view('posts.form', ['post' => $post, 'categories' => $categories]);
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validateData($request);

        $this->fillPost($post, $data, $request);
        $post->save();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Đã cập nhật bài viết "'.$post->title.'".');
    }

    public function destroy(Post $post)
    {
        $title = $post->title;
        if ($post->featured_image && ! str_starts_with($post->featured_image, 'http')) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Đã xóa bài viết "'.$title.'".');
    }

    public function toggleStatus(Post $post)
    {
        $post->status = $post->status === 'published' ? 'draft' : 'published';
        if ($post->status === 'published' && ! $post->published_at) {
            $post->published_at = now();
        }
        $post->save();

        return back()->with('success', 'Đã chuyển trạng thái bài viết thành "'.status_label($post->status).'".');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:post_categories,id',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'featured_image_file' => MediaService::imageRules(false),
            'featured_image_url' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'category_id.exists' => 'Chuyên mục đã chọn không tồn tại.',
            'featured_image_file.mimes' => 'Ảnh đại diện phải là jpg, jpeg, png, webp hoặc svg.',
            'featured_image_file.max' => 'Ảnh đại diện không được quá 5MB.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'published_at.date' => 'Ngày xuất bản không hợp lệ.',
        ]);
    }

    protected function fillPost(Post $post, array $data, Request $request): void
    {
        $post->fill([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'status' => $data['status'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        $slugSource = trim((string) ($data['slug'] ?? ''));
        $post->slug = SlugGenerator::unique(
            Post::class,
            $slugSource !== '' ? $slugSource : $data['title'],
            $post->exists ? $post->id : null
        );

        if ($data['status'] === 'published') {
            $post->published_at = ! empty($data['published_at']) ? $data['published_at'] : ($post->published_at ?? now());
        } else {
            $post->published_at = $data['published_at'] ?? $post->published_at;
        }

        if ($request->hasFile('featured_image_file')) {
            if ($post->featured_image && ! str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $media = MediaService::storeUpload($request->file('featured_image_file'), 'posts');
            $post->featured_image = $media->url;
        } elseif (! empty($data['featured_image_url'])) {
            $post->featured_image = $data['featured_image_url'];
        }
    }
}

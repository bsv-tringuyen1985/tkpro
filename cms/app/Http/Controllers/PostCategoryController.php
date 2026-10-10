<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:post_categories.view')->only(['index', 'show']);
        $this->middleware('permission:post_categories.create')->only(['create', 'store']);
        $this->middleware('permission:post_categories.update')->only(['edit', 'update']);
        $this->middleware('permission:post_categories.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = PostCategory::withCount('posts')->orderBy('sort_order')->orderBy('name');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where('name', 'like', '%'.$q.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('post_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('post_categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $category = new PostCategory();
        $category->fill($data);
        $category->slug = $this->resolveSlug($data, null);
        $category->save();

        return redirect()->route('admin.post_categories.index')
            ->with('success', 'Đã thêm chuyên mục "'.$category->name.'".');
    }

    public function edit(PostCategory $postCategory)
    {
        return view('post_categories.form', ['category' => $postCategory]);
    }

    public function update(Request $request, PostCategory $postCategory)
    {
        $data = $this->validateData($request);

        $postCategory->fill($data);
        $postCategory->slug = $this->resolveSlug($data, $postCategory);
        $postCategory->save();

        return redirect()->route('admin.post_categories.index')
            ->with('success', 'Đã cập nhật chuyên mục "'.$postCategory->name.'".');
    }

    public function destroy(PostCategory $postCategory)
    {
        if ($postCategory->posts()->count() > 0) {
            return back()->with('error', 'Không thể xóa chuyên mục "'.$postCategory->name.'" vì vẫn còn bài viết thuộc chuyên mục này.');
        }

        $name = $postCategory->name;
        $postCategory->delete();

        return redirect()->route('admin.post_categories.index')
            ->with('success', 'Đã xóa chuyên mục "'.$name.'".');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ], [
            'name.required' => 'Vui lòng nhập tên chuyên mục.',
            'status.required' => 'Vui lòng chọn trạng thái.',
        ]);
    }

    protected function resolveSlug(array $data, ?PostCategory $category): string
    {
        $source = trim((string) ($data['slug'] ?? ''));

        return SlugGenerator::unique(
            PostCategory::class,
            $source !== '' ? $source : $data['name'],
            $category?->id
        );
    }
}

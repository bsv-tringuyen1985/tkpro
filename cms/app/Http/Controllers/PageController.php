<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\MediaService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pages.view')->only(['index', 'show']);
        $this->middleware('permission:pages.create')->only(['create', 'store']);
        $this->middleware('permission:pages.update')->only(['edit', 'update']);
        $this->middleware('permission:pages.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Page::orderBy('sort_order')->orderBy('title');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where('title', 'like', '%'.$q.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $pages = $query->paginate(15)->withQueryString();

        return view('pages.index', compact('pages'));
    }

    public function create()
    {
        return view('pages.form', ['page' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $page = new Page();
        $this->fillPage($page, $data, $request);
        $page->save();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Đã thêm trang "'.$page->title.'".');
    }

    public function edit(Page $page)
    {
        return view('pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validateData($request);

        $this->fillPage($page, $data, $request);
        $page->save();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Đã cập nhật trang "'.$page->title.'".');
    }

    public function destroy(Page $page)
    {
        $title = $page->title;
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Đã xóa trang "'.$title.'".');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'featured_image_file' => MediaService::imageRules(false),
            'featured_image_url' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề trang.',
            'featured_image_file.mimes' => 'Ảnh đại diện phải là jpg, jpeg, png, webp hoặc svg.',
            'featured_image_file.max' => 'Ảnh đại diện không được quá 5MB.',
            'status.required' => 'Vui lòng chọn trạng thái.',
        ]);
    }

    protected function fillPage(Page $page, array $data, Request $request): void
    {
        $page->fill([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'status' => $data['status'],
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $slugSource = trim((string) ($data['slug'] ?? ''));
        $page->slug = SlugGenerator::unique(
            Page::class,
            $slugSource !== '' ? $slugSource : $data['title'],
            $page->exists ? $page->id : null
        );

        if ($request->hasFile('featured_image_file')) {
            if ($page->featured_image && ! str_starts_with($page->featured_image, 'http')) {
                Storage::disk('public')->delete($page->featured_image);
            }
            $media = MediaService::storeUpload($request->file('featured_image_file'), 'pages');
            $page->featured_image = $media->url;
        } elseif (! empty($data['featured_image_url'])) {
            $page->featured_image = $data['featured_image_url'];
        }
    }
}

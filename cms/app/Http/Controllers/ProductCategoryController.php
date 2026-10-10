<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Support\MediaService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product_categories.view')->only(['index', 'show']);
        $this->middleware('permission:product_categories.create')->only(['create', 'store']);
        $this->middleware('permission:product_categories.update')->only(['edit', 'update']);
        $this->middleware('permission:product_categories.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = ProductCategory::with('parent')->withCount('products')->orderBy('sort_order')->orderBy('name');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where('name', 'like', '%'.$q.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('product_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product_categories.form', [
            'category' => null,
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $category = new ProductCategory();
        $category->fill($data);
        $category->slug = $this->resolveSlug($data, null);
        $category->image = $this->resolveImage($request, null);
        $category->save();

        return redirect()->route('admin.product_categories.index')
            ->with('success', 'Đã thêm danh mục "'.$category->name.'".');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product_categories.form', [
            'category' => $productCategory,
            'parents' => $this->parentOptions($productCategory->id),
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $this->validateData($request, $productCategory);

        $productCategory->fill($data);
        $productCategory->slug = $this->resolveSlug($data, $productCategory);
        $productCategory->image = $this->resolveImage($request, $productCategory);
        $productCategory->save();

        return redirect()->route('admin.product_categories.index')
            ->with('success', 'Đã cập nhật danh mục "'.$productCategory->name.'".');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->count() > 0) {
            return back()->with('error', 'Không thể xóa danh mục "'.$productCategory->name.'" vì vẫn còn sản phẩm thuộc danh mục này.');
        }

        if ($productCategory->children()->count() > 0) {
            return back()->with('error', 'Không thể xóa danh mục "'.$productCategory->name.'" vì vẫn còn danh mục con.');
        }

        $name = $productCategory->name;
        if ($productCategory->image) {
            Storage::disk('public')->delete($productCategory->image);
        }
        $productCategory->delete();

        return redirect()->route('admin.product_categories.index')
            ->with('success', 'Đã xóa danh mục "'.$name.'".');
    }

    protected function validateData(Request $request, ?ProductCategory $category = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'image_file' => MediaService::imageRules(false),
            'image_url' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];

        $data = $request->validate($rules, [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'image_file.mimes' => 'Ảnh danh mục phải là jpg, jpeg, png, webp hoặc svg.',
            'image_file.max' => 'Ảnh danh mục không được quá 5MB.',
        ]);

        // Không cho chọn chính nó (hoặc con của nó) làm danh mục cha.
        if ($category && ! empty($data['parent_id'])) {
            if ((int) $data['parent_id'] === $category->id) {
                abort(422, 'Danh mục cha không được là chính danh mục này.');
            }
        }

        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image_file'], $data['image_url']);

        return $data;
    }

    protected function resolveSlug(array $data, ?ProductCategory $category): string
    {
        $source = trim((string) ($data['slug'] ?? ''));

        return SlugGenerator::unique(
            ProductCategory::class,
            $source !== '' ? $source : $data['name'],
            $category?->id
        );
    }

    protected function resolveImage(Request $request, ?ProductCategory $category): ?string
    {
        if ($request->hasFile('image_file')) {
            if ($category?->image && ! str_starts_with($category->image, 'http')) {
                Storage::disk('public')->delete($category->image);
            }
            $media = MediaService::storeUpload($request->file('image_file'), 'categories');

            return $media->url;
        }

        $url = trim((string) $request->input('image_url', ''));

        return $url !== '' ? $url : ($category?->image ?? null);
    }

    protected function parentOptions(?int $excludeId = null): array
    {
        $query = ProductCategory::whereNull('parent_id')->orderBy('name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->pluck('name', 'id')->toArray();
    }
}

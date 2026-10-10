<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Support\MediaService;
use App\Support\SlugGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.update')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
        $this->middleware('permission:products.publish')->only(['toggleStatus', 'toggleFeatured']);
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->orderBy('sort_order')->orderByDesc('created_at');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('sku', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = $this->categoryOptions();

        return view('products.form', ['product' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $product = new Product();
        $this->fillProduct($product, $data, $request);
        $product->save();

        $this->syncGallery($product, $request);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã thêm sản phẩm "'.$product->name.'".');
    }

    public function edit(Product $product)
    {
        $product->load('images', 'category');
        $categories = $this->categoryOptions();

        return view('products.form', ['product' => $product, 'categories' => $categories]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);

        $this->fillProduct($product, $data, $request);
        $product->save();

        // Xóa ảnh gallery được chọn.
        $deleteIds = $request->input('delete_images', []);
        if (is_array($deleteIds) && $deleteIds !== []) {
            $images = ProductImage::where('product_id', $product->id)->whereIn('id', $deleteIds)->get();
            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $this->syncGallery($product, $request);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã cập nhật sản phẩm "'.$product->name.'".');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm "'.$name.'".');
    }

    public function toggleStatus(Product $product)
    {
        $product->status = $product->status === 'published' ? 'draft' : 'published';
        $product->save();

        return back()->with('success', 'Đã chuyển trạng thái sản phẩm thành "'.status_label($product->status).'".');
    }

    public function toggleFeatured(Product $product)
    {
        $product->featured = ! $product->featured;
        $product->save();

        return back()->with('success', $product->featured ? 'Đã gắn nổi bật cho sản phẩm.' : 'Đã bỏ nổi bật cho sản phẩm.');
    }

    /**
     * Validate dữ liệu sản phẩm với thông báo tiếng Việt.
     */
    protected function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku'.($product ? ','.$product->id : ''),
            'category_id' => 'required|exists:product_categories,id',
            'price' => 'nullable|integer|min:0|max:99999999999',
            'price_label' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'technical_specifications' => 'nullable|string',
            'thumbnail_file' => MediaService::imageRules(false),
            'thumbnail_url' => 'nullable|string|max:500',
            'gallery.*' => MediaService::imageRules(false),
            'featured' => 'nullable|boolean',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được quá 255 ký tự.',
            'sku.unique' => 'Mã SKU này đã được dùng cho sản phẩm khác.',
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'category_id.exists' => 'Danh mục đã chọn không tồn tại.',
            'price.integer' => 'Giá phải là số nguyên (VNĐ).',
            'price.min' => 'Giá không được là số âm.',
            'thumbnail_file.mimes' => 'Ảnh đại diện phải là jpg, jpeg, png, webp hoặc svg.',
            'thumbnail_file.max' => 'Ảnh đại diện không được quá 5MB.',
            'gallery.*.mimes' => 'Ảnh trong bộ sưu tập phải là jpg, jpeg, png, webp hoặc svg.',
            'gallery.*.max' => 'Mỗi ảnh trong bộ sưu tập không được quá 5MB.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);
    }

    /**
     * Đổ dữ liệu vào model sản phẩm (slug, thumbnail, thông số kỹ thuật).
     */
    protected function fillProduct(Product $product, array $data, Request $request): void
    {
        $product->fill([
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'price_label' => $data['price_label'] ?? null,
            'status' => $data['status'],
            'featured' => $request->boolean('featured'),
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        // Slug: cho phép nhập tay, nếu trống tự sinh từ tên; luôn đảm bảo unique.
        $slugSource = trim((string) ($data['slug'] ?? ''));
        $product->slug = SlugGenerator::unique(
            Product::class,
            $slugSource !== '' ? $slugSource : $data['name'],
            $product->exists ? $product->id : null
        );

        // Thông số kỹ thuật: mỗi dòng "Tên thông số: Giá trị" -> JSON.
        $product->technical_specifications = $this->parseSpecifications($data['technical_specifications'] ?? null);

        // Ảnh đại diện: ưu tiên file upload, sau đó tới URL chọn từ thư viện.
        if ($request->hasFile('thumbnail_file')) {
            if ($product->thumbnail && ! str_starts_with($product->thumbnail, 'http')) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $media = MediaService::storeUpload($request->file('thumbnail_file'), 'products');
            $product->thumbnail = $media->url;
        } elseif (! empty($data['thumbnail_url'])) {
            $product->thumbnail = $data['thumbnail_url'];
        }
    }

    /**
     * Lưu các ảnh gallery mới upload.
     */
    protected function syncGallery(Product $product, Request $request): void
    {
        $files = $request->file('gallery', []);

        if (! is_array($files)) {
            return;
        }

        $sortOrder = (int) ($product->images()->max('sort_order') ?? -1);

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $sortOrder++;
            $media = MediaService::storeUpload($file, 'products');

            $product->images()->create([
                'path' => $media->path,
                'url' => $media->url,
                'filename' => $media->filename,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'alt' => $product->name,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    /**
     * "Tên: Giá trị" mỗi dòng -> danh sách label/value theo contract API.
     */
    protected function parseSpecifications(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $specs = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key !== '') {
                $specs[] = ['label' => $key, 'value' => $value];
            }
        }

        return $specs === [] ? null : $specs;
    }

    /**
     * Danh sách danh mục dạng cây (cha — con) cho select.
     */
    protected function categoryOptions(): array
    {
        $options = [];
        $roots = ProductCategory::whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get();

        foreach ($roots as $root) {
            $options[$root->id] = $root->name;
            $children = ProductCategory::where('parent_id', $root->id)->orderBy('sort_order')->orderBy('name')->get();
            foreach ($children as $child) {
                $options[$child->id] = '— '.$child->name;
            }
        }

        return $options;
    }
}

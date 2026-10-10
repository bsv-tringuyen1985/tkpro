<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Support\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banners.view')->only(['index', 'show']);
        $this->middleware('permission:banners.create')->only(['create', 'store']);
        $this->middleware('permission:banners.update')->only(['edit', 'update']);
        $this->middleware('permission:banners.delete')->only(['destroy']);
        $this->middleware('permission:banners.publish')->only(['toggleActive']);
    }

    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderByDesc('created_at')->paginate(15);

        return view('banners.index', compact('banners'));
    }

    public function create()
    {
        return view('banners.form', ['banner' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $banner = new Banner();
        $this->fillBanner($banner, $data, $request);
        $banner->save();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Đã thêm banner "'.$banner->title.'".');
    }

    public function edit(Banner $banner)
    {
        return view('banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validateData($request);

        $this->fillBanner($banner, $data, $request);
        $banner->save();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Đã cập nhật banner "'.$banner->title.'".');
    }

    public function destroy(Banner $banner)
    {
        $title = $banner->title;
        foreach (['image_desktop', 'image_mobile'] as $field) {
            if ($banner->{$field} && ! str_starts_with($banner->{$field}, 'http')) {
                Storage::disk('public')->delete($banner->{$field});
            }
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Đã xóa banner "'.$title.'".');
    }

    public function toggleActive(Banner $banner)
    {
        $banner->is_active = ! $banner->is_active;
        $banner->save();

        return back()->with('success', $banner->is_active ? 'Đã bật banner.' : 'Đã tắt banner.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'image_desktop_file' => MediaService::imageRules(false),
            'image_desktop_url' => 'nullable|string|max:500',
            'image_mobile_file' => MediaService::imageRules(false),
            'image_mobile_url' => 'nullable|string|max:500',
            'link' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề banner.',
            'image_desktop_file.mimes' => 'Ảnh desktop phải là jpg, jpeg, png, webp hoặc svg.',
            'image_desktop_file.max' => 'Ảnh desktop không được quá 5MB.',
            'image_mobile_file.mimes' => 'Ảnh mobile phải là jpg, jpeg, png, webp hoặc svg.',
            'image_mobile_file.max' => 'Ảnh mobile không được quá 5MB.',
        ]);
    }

    protected function fillBanner(Banner $banner, array $data, Request $request): void
    {
        $banner->fill([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'link' => $data['link'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $banner->image_desktop = $this->resolveImage($request, $banner, 'image_desktop');
        $banner->image_mobile = $this->resolveImage($request, $banner, 'image_mobile');
    }

    protected function resolveImage(Request $request, Banner $banner, string $field): ?string
    {
        $fileKey = $field.'_file';
        $urlKey = $field.'_url';

        if ($request->hasFile($fileKey)) {
            $current = $banner->{$field};
            if ($current && ! str_starts_with($current, 'http')) {
                Storage::disk('public')->delete($current);
            }
            $media = MediaService::storeUpload($request->file($fileKey), 'banners');

            return $media->url;
        }

        $url = trim((string) $request->input($urlKey, ''));

        return $url !== '' ? $url : ($banner->{$field} ?? null);
    }
}

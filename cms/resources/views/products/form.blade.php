@extends('layouts.admin')

@section('title', $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Sản phẩm</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $product ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-1">{{ $product ? '✏️ Sửa sản phẩm' : '➕ Thêm sản phẩm' }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $product ? 'Cập nhật thông tin sản phẩm "'.$product->name.'".' : 'Điền thông tin để thêm sản phẩm mới lên website.' }}</p>

    <form method="POST" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($product) @method('PUT') @endif

        {{-- Thông tin cơ bản --}}
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">📝 Thông tin cơ bản</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required placeholder="VD: Camera WiFi 2K trong nhà"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Đường dẫn (slug)</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu để trống"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <p class="text-xs text-slate-400 mt-1">Chữ thường, không dấu, nối bằng gạch ngang. VD: camera-wifi-2k</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Mã SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" placeholder="VD: CAM-WIFI-2K"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Danh mục <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <option value="">— Chưa phân loại —</option>
                        @foreach($categories as $id => $label)
                        <option value="{{ $id }}" {{ (string) old('category_id', $product->category_id ?? '') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Giá bán (VNĐ)</label>
                    <input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0" step="1000" placeholder="VD: 890000 — để trống = Liên hệ"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <p class="text-xs text-slate-400 mt-1">Nhập số, không cần dấu chấm/phẩy. Để trống để hiển thị "Liên hệ".</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Nhãn giá thay thế</label>
                    <input type="text" name="price_label" value="{{ old('price_label', $product->price_label ?? '') }}" placeholder="VD: Giá tham khảo — hiển thị thay cho giá số nếu có"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
            </div>
        </div>

        {{-- Ảnh sản phẩm --}}
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">🖼️ Ảnh sản phẩm</h2>

            <div class="mb-5">
                <label class="block text-sm font-semibold mb-1.5">Ảnh đại diện</label>
                <div class="flex flex-wrap items-center gap-3">
                    <img id="thumbnail_url_preview" src="{{ old('thumbnail_url', $product->thumbnail ?? '') }}" alt=""
                         class="{{ old('thumbnail_url', $product->thumbnail ?? '') ? '' : 'hidden' }} w-20 h-20 object-cover rounded-lg border">
                    <input type="file" name="thumbnail_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                           class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                    <button type="button" onclick="openMediaPicker('thumbnail_url')" class="text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-3 py-2">🖼️ Chọn từ thư viện</button>
                </div>
                <input type="hidden" id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $product->thumbnail ?? '') }}">
                <p class="text-xs text-slate-400 mt-1">jpg, jpeg, png, webp, svg — tối đa 5MB. Nếu vừa tải file mới vừa chọn từ thư viện, file mới sẽ được ưu tiên.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Bộ sưu tập ảnh (nhiều ảnh)</label>
                <input type="file" name="gallery[]" multiple accept=".jpg,.jpeg,.png,.webp,.svg"
                       class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                <p class="text-xs text-slate-400 mt-1">Có thể chọn nhiều ảnh cùng lúc. Mỗi ảnh tối đa 5MB.</p>

                @if($product && $product->images->isNotEmpty())
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mt-4">
                    @foreach($product->images as $img)
                    <div class="relative group border rounded-lg overflow-hidden">
                        <img src="{{ $img->url }}" alt="" class="w-full h-24 object-cover">
                        <label class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-xs py-1.5 text-center cursor-pointer opacity-0 group-hover:opacity-100 transition">
                            <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="mr-1"> 🗑️ Xóa ảnh này
                        </label>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-2">Di chuột lên ảnh và tick để xóa ảnh khỏi bộ sưu tập.</p>
                @endif
            </div>
        </div>

        {{-- Mô tả --}}
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">📄 Mô tả</h2>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Mô tả ngắn</label>
                    <textarea name="short_description" rows="2" placeholder="1–2 câu giới thiệu nổi bật của sản phẩm…"
                              class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold">Mô tả chi tiết</label>
                        <button type="button" onclick="openMediaPicker('description')" class="text-xs border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-2.5 py-1.5">🖼️ Chèn ảnh vào nội dung</button>
                    </div>
                    <textarea id="description" name="description" rows="8" placeholder="Mô tả chi tiết sản phẩm. Có thể dùng HTML cơ bản (p, ul, li, strong, img)…"
                              class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 font-mono">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Thông số kỹ thuật</label>
                    <textarea name="technical_specifications" rows="5" placeholder="Mỗi dòng một thông số theo dạng &#10;Độ phân giải: 2K (2304×1296)&#10;Kết nối: WiFi 2.4GHz&#10;Bảo hành: 24 tháng"
                              class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 font-mono">@php
$specs = old('technical_specifications');
if ($specs === null && $product && is_array($product->technical_specifications)) {
    $lines = [];
    foreach ($product->technical_specifications as $k => $v) {
        $lines[] = is_array($v) ? ($v['label'] ?? '').': '.($v['value'] ?? '') : $k.': '.$v;
    }
    $specs = implode("\n", $lines);
}
echo e($specs ?? '');
@endphp</textarea>
                    <p class="text-xs text-slate-400 mt-1">Mỗi dòng một thông số, theo dạng <code>Tên thông số: Giá trị</code>.</p>
                </div>
            </div>
        </div>

        {{-- Hiển thị & SEO --}}
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">⚙️ Hiển thị & SEO</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <option value="draft" {{ old('status', $product->status ?? 'draft') === 'draft' ? 'selected' : '' }}>📝 Bản nháp (ẩn trên web)</option>
                        <option value="published" {{ old('status', $product->status ?? '') === 'published' ? 'selected' : '' }}>✅ Đã xuất bản (hiện trên web)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <p class="text-xs text-slate-400 mt-1">Số nhỏ hiện trước.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured ?? false) ? 'checked' : '' }} class="w-4 h-4">
                        <span class="font-semibold">⭐ Gắn nổi bật (hiện ở mục sản phẩm nổi bật trang chủ)</span>
                    </label>
                </div>
                <div class="md:col-span-2 border-t border-slate-100 pt-4">
                    <p class="text-sm font-semibold mb-3">🔍 SEO <span class="font-normal text-slate-400">(để trống sẽ tự dùng tên/mô tả)</span></p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta description</label>
                    <textarea name="meta_description" rows="2" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta keywords</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}" placeholder="camera wifi, camera 2k…"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $product ? '💾 Lưu thay đổi' : '➕ Thêm sản phẩm' }}</button>
            <a href="{{ route('admin.products.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

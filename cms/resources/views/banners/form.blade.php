@extends('layouts.admin')

@section('title', $banner ? 'Sửa banner' : 'Thêm banner')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.banners.index') }}" class="hover:text-brand-700">Banner</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $banner ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $banner ? '✏️ Sửa banner' : '➕ Thêm banner' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Banner hiển thị ở khu vực nổi bật trang chủ.</p>

    <form method="POST" action="{{ $banner ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($banner) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tiêu đề <span class="text-danger">*</span></label>
            <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" required placeholder="VD: Nâng cấp không gian, làm chủ công nghệ."
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tiêu đề phụ</label>
            <textarea name="subtitle" rows="2" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('subtitle', $banner->subtitle ?? '') }}</textarea>
        </div>

        @foreach(['image_desktop' => 'Ảnh desktop (màn hình lớn)', 'image_mobile' => 'Ảnh mobile (màn hình nhỏ)'] as $field => $label)
        <div>
            <label class="block text-sm font-semibold mb-1.5">{{ $label }}</label>
            <div class="flex flex-wrap items-center gap-3">
                <img id="{{ $field }}_url_preview" src="{{ old($field.'_url', $banner->$field ?? '') }}" alt=""
                     class="{{ old($field.'_url', $banner->$field ?? '') ? '' : 'hidden' }} w-32 h-16 object-cover rounded-lg border">
                <input type="file" name="{{ $field }}_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                       class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                <button type="button" onclick="openMediaPicker('{{ $field }}_url')" class="text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-3 py-2">🖼️ Chọn từ thư viện</button>
            </div>
            <input type="hidden" id="{{ $field }}_url" name="{{ $field }}_url" value="{{ old($field.'_url', $banner->$field ?? '') }}">
        </div>
        @endforeach

        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Liên kết khi bấm vào</label>
                <input type="text" name="link" value="{{ old('link', $banner->link ?? '') }}" placeholder="VD: /san-pham"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">Chữ trên nút bấm</label>
                <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text ?? '') }}" placeholder="VD: Xem sản phẩm"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
            <div class="flex items-end pb-3">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-semibold">✅ Kích hoạt (hiển thị trên web)</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $banner ? '💾 Lưu thay đổi' : '➕ Thêm banner' }}</button>
            <a href="{{ route('admin.banners.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

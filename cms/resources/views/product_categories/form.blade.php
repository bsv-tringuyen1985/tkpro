@extends('layouts.admin')

@section('title', $category ? 'Sửa danh mục' : 'Thêm danh mục')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.product_categories.index') }}" class="hover:text-brand-700">Danh mục sản phẩm</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $category ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $category ? '✏️ Sửa danh mục' : '➕ Thêm danh mục' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Danh mục có thể lồng nhau nhiều cấp (danh mục cha — danh mục con).</p>

    <form method="POST" action="{{ $category ? route('admin.product_categories.update', $category) : route('admin.product_categories.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($category) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="VD: Camera giám sát"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Đường dẫn (slug)</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu để trống"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Danh mục cha</label>
            <select name="parent_id" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <option value="">— Là danh mục gốc —</option>
                @foreach($parents as $id => $label)
                <option value="{{ $id }}" {{ (string) old('parent_id', $category->parent_id ?? '') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Ảnh danh mục</label>
            <div class="flex flex-wrap items-center gap-3">
                <img id="image_url_preview" src="{{ old('image_url', $category->image ?? '') }}" alt=""
                     class="{{ old('image_url', $category->image ?? '') ? '' : 'hidden' }} w-20 h-20 object-cover rounded-lg border">
                <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                       class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                <button type="button" onclick="openMediaPicker('image_url')" class="text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-3 py-2">🖼️ Chọn từ thư viện</button>
            </div>
            <input type="hidden" id="image_url" name="image_url" value="{{ old('image_url', $category->image ?? '') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Mô tả</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('description', $category->description ?? '') }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Trạng thái <span class="text-danger">*</span></label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                    <option value="published" {{ old('status', $category->status ?? 'published') === 'published' ? 'selected' : '' }}>✅ Đã xuất bản</option>
                    <option value="draft" {{ old('status', $category->status ?? '') === 'draft' ? 'selected' : '' }}>📝 Bản nháp</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <p class="text-sm font-semibold mb-3">🔍 SEO</p>
            <div class="space-y-4">
                <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}" placeholder="Meta title"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <textarea name="meta_description" rows="2" placeholder="Meta description"
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $category ? '💾 Lưu thay đổi' : '➕ Thêm danh mục' }}</button>
            <a href="{{ route('admin.product_categories.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

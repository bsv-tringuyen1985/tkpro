@extends('layouts.admin')

@section('title', $category ? 'Sửa chuyên mục' : 'Thêm chuyên mục')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.post_categories.index') }}" class="hover:text-brand-700">Chuyên mục bài viết</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $category ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $category ? '✏️ Sửa chuyên mục' : '➕ Thêm chuyên mục' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Chuyên mục giúp độc giả tìm bài viết theo chủ đề.</p>

    <form method="POST" action="{{ $category ? route('admin.post_categories.update', $category) : route('admin.post_categories.store') }}" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($category) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tên chuyên mục <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="VD: Khuyến mãi"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Đường dẫn (slug)</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu để trống"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
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

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $category ? '💾 Lưu thay đổi' : '➕ Thêm chuyên mục' }}</button>
            <a href="{{ route('admin.post_categories.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', $page ? 'Sửa trang' : 'Thêm trang')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.pages.index') }}" class="hover:text-brand-700">Trang tĩnh</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $page ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-1">{{ $page ? '✏️ Sửa trang' : '➕ Thêm trang' }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $page ? 'Cập nhật nội dung trang "'.$page->title.'".' : 'Tạo trang nội dung mới cho website.' }}</p>

    <form method="POST" action="{{ $page ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($page) @method('PUT') @endif

        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Tiêu đề trang <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" required placeholder="VD: Giới thiệu"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Đường dẫn (slug)</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}" placeholder="Tự động tạo từ tiêu đề nếu để trống"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                        <option value="published" {{ old('status', $page->status ?? 'published') === 'published' ? 'selected' : '' }}>✅ Đã xuất bản</option>
                        <option value="draft" {{ old('status', $page->status ?? '') === 'draft' ? 'selected' : '' }}>📝 Bản nháp</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Ảnh đại diện</label>
                <div class="flex flex-wrap items-center gap-3">
                    <img id="featured_image_url_preview" src="{{ old('featured_image_url', $page->featured_image ?? '') }}" alt=""
                         class="{{ old('featured_image_url', $page->featured_image ?? '') ? '' : 'hidden' }} w-20 h-20 object-cover rounded-lg border">
                    <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                           class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                    <button type="button" onclick="openMediaPicker('featured_image_url')" class="text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-3 py-2">🖼️ Chọn từ thư viện</button>
                </div>
                <input type="hidden" id="featured_image_url" name="featured_image_url" value="{{ old('featured_image_url', $page->featured_image ?? '') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Tóm tắt</label>
                <textarea name="excerpt" rows="2" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-semibold">Nội dung trang</label>
                    <button type="button" onclick="openMediaPicker('content')" class="text-xs border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-2.5 py-1.5">🖼️ Chèn ảnh vào nội dung</button>
                </div>
                <textarea id="content" name="content" rows="14" placeholder="Soạn nội dung trang. Có thể dùng HTML cơ bản…"
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 font-mono">{{ old('content', $page->content ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0"
                       class="w-full md:w-48 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">🔍 SEO</h2>
            <div class="space-y-4">
                <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}" placeholder="Meta title"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <textarea name="meta_description" rows="2" placeholder="Meta description"
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $page ? '💾 Lưu thay đổi' : '➕ Thêm trang' }}</button>
            <a href="{{ route('admin.pages.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

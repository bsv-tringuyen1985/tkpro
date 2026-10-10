@extends('layouts.admin')

@section('title', $post ? 'Sửa bài viết' : 'Đăng bài viết')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.posts.index') }}" class="hover:text-brand-700">Bài viết</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $post ? 'Sửa' : 'Đăng mới' }}</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-1">{{ $post ? '✏️ Sửa bài viết' : '➕ Đăng bài viết' }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $post ? 'Cập nhật nội dung bài viết.' : 'Soạn bài viết mới cho website.' }}</p>

    <form method="POST" action="{{ $post ? route('admin.posts.update', $post) : route('admin.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($post) @method('PUT') @endif

        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required placeholder="VD: Combo camera gia đình — bảo vệ ngôi nhà 24/7"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Đường dẫn (slug)</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}" placeholder="Tự động tạo từ tiêu đề nếu để trống"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Chuyên mục</label>
                    <select name="category_id" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <option value="">— Chưa phân loại —</option>
                        @foreach($categories as $id => $label)
                        <option value="{{ $id }}" {{ (string) old('category_id', $post->category_id ?? '') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Ảnh đại diện</label>
                <div class="flex flex-wrap items-center gap-3">
                    <img id="featured_image_url_preview" src="{{ old('featured_image_url', $post->featured_image ?? '') }}" alt=""
                         class="{{ old('featured_image_url', $post->featured_image ?? '') ? '' : 'hidden' }} w-20 h-20 object-cover rounded-lg border">
                    <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                           class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                    <button type="button" onclick="openMediaPicker('featured_image_url')" class="text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-3 py-2">🖼️ Chọn từ thư viện</button>
                </div>
                <input type="hidden" id="featured_image_url" name="featured_image_url" value="{{ old('featured_image_url', $post->featured_image ?? '') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Tóm tắt</label>
                <textarea name="excerpt" rows="2" placeholder="1–2 câu tóm tắt hiển thị ở danh sách bài viết…"
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-semibold">Nội dung bài viết</label>
                    <button type="button" onclick="openMediaPicker('content')" class="text-xs border border-brand-600 text-brand-700 hover:bg-brand-50 font-semibold rounded-lg px-2.5 py-1.5">🖼️ Chèn ảnh vào nội dung</button>
                </div>
                <textarea id="content" name="content" rows="14" placeholder="Soạn nội dung bài viết. Có thể dùng HTML cơ bản (p, h2, h3, ul, li, strong, img)…"
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 font-mono">{{ old('content', $post->content ?? '') }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Mẹo: bấm "Chèn ảnh vào nội dung" rồi đặt con trỏ chuột ở vị trí muốn chèn trước khi chọn ảnh.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <h2 class="font-bold mb-4">⚙️ Xuất bản & SEO</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                        <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>📝 Bản nháp</option>
                        <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>✅ Đã xuất bản</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Ngày xuất bản</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $post && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                    <p class="text-xs text-slate-400 mt-1">Để trống sẽ lấy thời điểm hiện tại khi xuất bản.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta description</label>
                    <textarea name="meta_description" rows="2" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Meta keywords</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $post ? '💾 Lưu thay đổi' : '➕ Đăng bài viết' }}</button>
            <a href="{{ route('admin.posts.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Thư viện ảnh')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Thư viện ảnh</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🖼️ Thư viện ảnh</h1>
        <p class="text-sm text-slate-500 mt-1">Tải ảnh lên một lần, dùng lại ở mọi nơi (sản phẩm, bài viết, banner…).</p>
    </div>
</div>

@if(has_permission('media.create'))
<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
      class="bg-white rounded-2xl border-2 border-dashed border-brand-200 p-6 mb-5 text-center hover:border-brand-400 transition">
    @csrf
    <div class="text-4xl mb-2">📤</div>
    <p class="font-semibold text-slate-700">Kéo thả hoặc chọn ảnh để tải lên</p>
    <p class="text-xs text-slate-400 mt-1 mb-3">jpg, jpeg, png, webp, svg — tối đa 5MB/ảnh, tối đa 10 ảnh mỗi lần</p>
    <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.webp,.svg" required
           class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white file:font-semibold hover:file:bg-brand-700">
    <button class="ml-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2">Tải lên</button>
</form>
@endif

<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tên file…"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Loại</label>
        <select name="type" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">— Tất cả —</option>
            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Ảnh</option>
            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Khác</option>
        </select>
    </div>
    <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍 Lọc</button>
</form>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm p-5">
    @if($media->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">🖼️</div>
            <p class="font-semibold text-slate-700">Thư viện đang trống</p>
            <p class="text-sm text-slate-500 mt-1">Tải ảnh lên để bắt đầu sử dụng.</p>
        </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($media as $m)
        <div class="group border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition relative">
            <div class="aspect-square bg-slate-50 flex items-center justify-center overflow-hidden cursor-pointer" onclick="showPreview('{{ $m->url }}', '{{ e($m->filename) }}')">
                @if($m->isImage())
                    <img src="{{ $m->url }}" alt="{{ e($m->alt ?? '') }}" class="w-full h-full object-cover" loading="lazy">
                @else
                    <span class="text-4xl">📄</span>
                @endif
            </div>
            <div class="p-2">
                <p class="text-xs font-medium truncate" title="{{ e($m->filename) }}">{{ e($m->filename) }}</p>
                <p class="text-[.68rem] text-slate-400">{{ $m->humanSize() }}@if($m->width) · {{ $m->width }}×{{ $m->height }}@endif</p>
            </div>
            @if(has_permission('media.delete'))
            <form method="POST" action="{{ route('admin.media.destroy', $m) }}" data-confirm="Xóa ảnh &quot;{{ e($m->filename) }}&quot; khỏi thư viện? Các nơi đang dùng ảnh này có thể bị lỗi hiển thị."
                  class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition">
                @csrf @method('DELETE')
                <button class="bg-white/95 hover:bg-red-50 text-danger text-xs font-bold rounded-lg px-2 py-1.5 shadow">🗑️</button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $media->links() }}</div>
    @endif
</div>

{{-- Modal xem trước --}}
<div id="previewModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/70" onclick="document.getElementById('previewModal').classList.add('hidden')"></div>
    <div class="absolute inset-4 md:inset-16 bg-white rounded-2xl shadow-2xl p-4 flex flex-col items-center justify-center overflow-hidden">
        <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="absolute top-3 right-4 text-2xl text-slate-400 hover:text-slate-700">✕</button>
        <img id="previewImg" src="" alt="" class="max-h-[80%] max-w-full rounded-lg shadow">
        <p id="previewName" class="text-sm text-slate-500 mt-3"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showPreview(url, name) {
    document.getElementById('previewImg').src = url;
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewModal').classList.remove('hidden');
}
</script>
@endpush

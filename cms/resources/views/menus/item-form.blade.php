@extends('layouts.admin')

@section('title', ($item ? 'Sửa mục menu' : 'Thêm mục menu').': '.$menu->name)

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.menus.index') }}" class="hover:text-brand-700">Menu</a>
<span class="text-slate-300">/</span><a href="{{ route('admin.menu_items.index', $menu) }}" class="hover:text-brand-700">{{ $menu->name }}</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $item ? 'Sửa mục' : 'Thêm mục' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $item ? '✏️ Sửa mục menu' : '➕ Thêm mục menu' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Menu: <strong>{{ $menu->name }}</strong></p>

    <form method="POST" action="{{ $item ? route('admin.menu_items.update', [$menu, $item]) : route('admin.menu_items.store', $menu) }}" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($item) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tiêu đề <span class="text-danger">*</span></label>
            <input type="text" name="title" value="{{ old('title', $item->title ?? '') }}" required placeholder="VD: Sản phẩm"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Mục cha</label>
            <select name="parent_id" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                <option value="">— Là mục gốc —</option>
                @foreach($parents as $id => $label)
                <option value="{{ $id }}" {{ (string) old('parent_id', $item->parent_id ?? '') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Chọn mục cha để tạo menu lồng nhau (VD: "Camera" nằm dưới "Sản phẩm").</p>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Loại liên kết <span class="text-danger">*</span></label>
            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="url" {{ old('type', $item->type ?? 'url') === 'url' ? 'checked' : '' }} class="w-4 h-4" onchange="toggleMenuType()"> 🔗 Đường dẫn URL
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="page" {{ old('type', $item->type ?? '') === 'page' ? 'checked' : '' }} class="w-4 h-4" onchange="toggleMenuType()"> 📄 Trang tĩnh
                </label>
            </div>
        </div>

        <div id="urlField">
            <label class="block text-sm font-semibold mb-1.5">Đường dẫn URL <span class="text-danger">*</span></label>
            <input type="text" name="url" value="{{ old('url', $item->url ?? '') }}" placeholder="VD: /san-pham hoặc https://…"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div id="pageField" class="hidden">
            <label class="block text-sm font-semibold mb-1.5">Chọn trang <span class="text-danger">*</span></label>
            <select name="page_id" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                <option value="">— Chọn trang —</option>
                @foreach($pages as $id => $label)
                <option value="{{ $id }}" {{ (string) old('page_id', $item->page_id ?? '') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Mở liên kết</label>
                <select name="target" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                    <option value="_self" {{ old('target', $item->target ?? '_self') === '_self' ? 'selected' : '' }}>Cùng tab</option>
                    <option value="_blank" {{ old('target', $item->target ?? '') === '_blank' ? 'selected' : '' }}>Tab mới</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">Thứ tự</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
            <div class="flex items-end pb-3">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-semibold">Hiển thị</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $item ? '💾 Lưu thay đổi' : '➕ Thêm mục' }}</button>
            <a href="{{ route('admin.menu_items.index', $menu) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleMenuType() {
    var type = document.querySelector('input[name="type"]:checked').value;
    document.getElementById('urlField').classList.toggle('hidden', type !== 'url');
    document.getElementById('pageField').classList.toggle('hidden', type !== 'page');
}
toggleMenuType();
</script>
@endpush

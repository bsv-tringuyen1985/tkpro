@extends('layouts.admin')

@section('title', 'Sản phẩm')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Sản phẩm</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">📦 Sản phẩm</h1>
        <p class="text-sm text-slate-500 mt-1">Quản lý toàn bộ sản phẩm hiển thị trên website.</p>
    </div>
    @if(has_permission('products.create'))
    <a href="{{ route('admin.products.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm sản phẩm</a>
    @endif
</div>

{{-- Bộ lọc / tìm kiếm --}}
<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tên sản phẩm hoặc SKU…"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Danh mục</label>
        <select name="category_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            <option value="">— Tất cả —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Trạng thái</label>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            <option value="">— Tất cả —</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
        </select>
    </div>
    <label class="flex items-center gap-2 text-sm pb-2 cursor-pointer">
        <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }} class="w-4 h-4"> ⭐ Nổi bật
    </label>
    <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍 Lọc</button>
    @if(request()->hasAny(['q', 'category_id', 'status', 'featured']))
    <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-slate-700 underline">Xóa lọc</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($products->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">📦</div>
            <p class="font-semibold text-slate-700">Chưa có sản phẩm nào</p>
            <p class="text-sm text-slate-500 mt-1">Hãy thêm sản phẩm đầu tiên để hiển thị trên website.</p>
            @if(has_permission('products.create'))
            <a href="{{ route('admin.products.create') }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Thêm sản phẩm</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr>
                <th class="w-16">Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th class="text-right">Giá</th>
                <th>Trạng thái</th>
                <th class="text-right">Thao tác</th>
            </tr>
            @foreach($products as $p)
            <tr>
                <td>
                    @if($p->thumbnailUrl())
                        <img src="{{ $p->thumbnailUrl() }}" alt="" class="w-12 h-12 object-cover rounded-lg border">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-xl">📦</div>
                    @endif
                </td>
                <td>
                    <div class="font-semibold">{{ $p->name }}</div>
                    <div class="text-xs text-slate-400">SKU: {{ $p->sku ?? '—' }} · /{{ $p->slug }}</div>
                    @if($p->featured)<span class="text-xs text-amber-600 font-semibold">⭐ Nổi bật</span>@endif
                </td>
                <td class="text-sm text-slate-600">{{ $p->category->name ?? '—' }}</td>
                <td class="text-right font-semibold text-brand-700 whitespace-nowrap">{{ format_price($p->price) }}</td>
                <td>
                    @if(has_permission('products.publish'))
                    <form method="POST" action="{{ route('admin.products.toggle-status', $p) }}" class="inline">
                        @csrf
                        <button title="Nhấn để đổi trạng thái"
                                class="text-xs font-bold rounded-full px-2.5 py-1 {{ $p->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ status_label($p->status) }}
                        </button>
                    </form>
                    @else
                    <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $p->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ status_label($p->status) }}</span>
                    @endif
                </td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('products.publish'))
                        <form method="POST" action="{{ route('admin.products.toggle-featured', $p) }}">
                            @csrf
                            <button title="{{ $p->featured ? 'Bỏ nổi bật' : 'Gắn nổi bật' }}" class="text-sm border rounded-lg px-2 py-1 hover:bg-amber-50 {{ $p->featured ? 'border-amber-400' : 'border-slate-200' }}">⭐</button>
                        </form>
                        @endif
                        @if(has_permission('products.update'))
                        <a href="{{ route('admin.products.edit', $p) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('products.delete'))
                        <form method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="Xóa sản phẩm &quot;{{ $p->name }}&quot;? Hành động này không thể hoàn tác." class="inline">
                            @csrf @method('DELETE')
                            <button class="text-sm border border-red-200 rounded-lg px-2.5 py-1 hover:bg-red-50 text-danger font-semibold">🗑️ Xóa</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection

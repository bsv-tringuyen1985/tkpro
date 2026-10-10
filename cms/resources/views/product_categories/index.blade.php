@extends('layouts.admin')

@section('title', 'Danh mục sản phẩm')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Danh mục sản phẩm</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🗂️ Danh mục sản phẩm</h1>
        <p class="text-sm text-slate-500 mt-1">Danh mục nhiều cấp (cha — con) cho sản phẩm.</p>
    </div>
    @if(has_permission('product_categories.create'))
    <a href="{{ route('admin.product_categories.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm danh mục</a>
    @endif
</div>

<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tên danh mục…"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Trạng thái</label>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">— Tất cả —</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
        </select>
    </div>
    <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍 Lọc</button>
</form>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($categories->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">🗂️</div>
            <p class="font-semibold text-slate-700">Chưa có danh mục nào</p>
            @if(has_permission('product_categories.create'))
            <a href="{{ route('admin.product_categories.create') }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Thêm danh mục</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th>Tên danh mục</th><th>Danh mục cha</th><th class="text-center">Sản phẩm</th><th>Trạng thái</th><th class="text-right">Thao tác</th></tr>
            @foreach($categories as $c)
            <tr>
                <td>
                    <div class="font-semibold">{{ $c->name }}</div>
                    <div class="text-xs text-slate-400">/{{ $c->slug }} · Thứ tự: {{ $c->sort_order }}</div>
                </td>
                <td class="text-sm text-slate-600">{{ $c->parent->name ?? '—' }}</td>
                <td class="text-center"><span class="bg-brand-50 text-brand-700 text-xs font-bold rounded-full px-2.5 py-1">{{ $c->products_count }}</span></td>
                <td><span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $c->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ status_label($c->status) }}</span></td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('product_categories.update'))
                        <a href="{{ route('admin.product_categories.edit', $c) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('product_categories.delete'))
                        <form method="POST" action="{{ route('admin.product_categories.destroy', $c) }}" data-confirm="Xóa danh mục &quot;{{ $c->name }}&quot;?" class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $categories->links() }}</div>
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Menu')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Menu</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🧭 Menu</h1>
        <p class="text-sm text-slate-500 mt-1">Quản lý menu điều hướng header và footer của website.</p>
    </div>
    @if(has_permission('menus.create'))
    <a href="{{ route('admin.menus.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm menu</a>
    @endif
</div>

@if($menus->isEmpty())
<div class="bg-white rounded-2xl border border-emerald-900/10 p-12 text-center shadow-sm">
    <div class="text-5xl mb-3">🧭</div>
    <p class="font-semibold text-slate-700">Chưa có menu nào</p>
</div>
@else
<div class="grid lg:grid-cols-2 gap-6">
    @foreach($menus as $menu)
    <div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold">{{ $menu->name }}</h2>
                <p class="text-xs text-slate-400">Vị trí: <span class="font-semibold">{{ $menu->location === 'header' ? 'Đầu trang (header)' : 'Chân trang (footer)' }}</span> · slug: {{ $menu->slug }}</p>
            </div>
            <div class="flex gap-1.5">
                @if(has_permission('menus.update'))
                <a href="{{ route('admin.menu_items.index', $menu) }}" class="text-xs bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg px-3 py-2">🧩 Quản lý mục</a>
                <a href="{{ route('admin.menus.edit', $menu) }}" class="text-xs border border-slate-200 rounded-lg px-3 py-2 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                @endif
                @if(has_permission('menus.delete'))
                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" data-confirm="Xóa menu &quot;{{ $menu->name }}&quot; cùng toàn bộ mục con?" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-xs border border-red-200 rounded-lg px-3 py-2 hover:bg-red-50 text-danger font-semibold">🗑️</button>
                </form>
                @endif
            </div>
        </div>
        <div class="p-5">
            @if($menu->rootItems->isEmpty())
                <p class="text-sm text-slate-400 italic">Chưa có mục nào. Bấm "Quản lý mục" để thêm.</p>
            @else
            <ul class="space-y-1.5 text-sm">
                @foreach($menu->rootItems as $item)
                <li>
                    <div class="flex items-center gap-2 bg-slate-50 rounded-lg px-3 py-2">
                        <span class="font-medium">{{ $item->title }}</span>
                        <span class="text-xs text-slate-400 truncate">{{ $item->resolvedUrl() }}</span>
                        @if(!$item->is_active)<span class="text-xs text-slate-400">(tắt)</span>@endif
                    </div>
                    @if($item->children->isNotEmpty())
                    <ul class="ml-6 mt-1.5 space-y-1.5">
                        @foreach($item->children as $child)
                        <li class="flex items-center gap-2 bg-slate-50/60 rounded-lg px-3 py-1.5">
                            <span>↳</span><span>{{ $child->title }}</span>
                            <span class="text-xs text-slate-400 truncate">{{ $child->resolvedUrl() }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

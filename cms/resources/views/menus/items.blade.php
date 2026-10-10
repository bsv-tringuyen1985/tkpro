@extends('layouts.admin')

@section('title', 'Mục menu: '.$menu->name)

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.menus.index') }}" class="hover:text-brand-700">Menu</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $menu->name }}</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🧩 Mục menu: {{ $menu->name }}</h1>
        <p class="text-sm text-slate-500 mt-1">Sắp xếp các liên kết theo thứ tự hiển thị (số nhỏ hiện trước). Mục con nằm thụt vào dưới mục cha.</p>
    </div>
    @if(has_permission('menus.create'))
    <a href="{{ route('admin.menu_items.create', $menu) }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm mục</a>
    @endif
</div>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($menu->rootItems->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">🧩</div>
            <p class="font-semibold text-slate-700">Menu này chưa có mục nào</p>
            @if(has_permission('menus.create'))
            <a href="{{ route('admin.menu_items.create', $menu) }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Thêm mục đầu tiên</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th>Tiêu đề</th><th>Loại</th><th>Liên kết</th><th class="text-center">Thứ tự</th><th>Trạng thái</th><th class="text-right">Thao tác</th></tr>
            @foreach($menu->rootItems as $item)
                @include('menus._item-row', ['item' => $item, 'menu' => $menu, 'depth' => 0])
                @foreach($item->children as $child)
                    @include('menus._item-row', ['item' => $child, 'menu' => $menu, 'depth' => 1])
                @endforeach
            @endforeach
        </table>
    </div>
    @endif
</div>

<div class="mt-4">
    <a href="{{ route('admin.menus.index') }}" class="text-sm text-brand-700 hover:underline">← Quay lại danh sách menu</a>
</div>
@endsection

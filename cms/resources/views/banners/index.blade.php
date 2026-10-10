@extends('layouts.admin')

@section('title', 'Banner')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Banner</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🖼️ Banner</h1>
        <p class="text-sm text-slate-500 mt-1">Banner quảng cáo hiển thị ở trang chủ website.</p>
    </div>
    @if(has_permission('banners.create'))
    <a href="{{ route('admin.banners.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm banner</a>
    @endif
</div>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($banners->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">🖼️</div>
            <p class="font-semibold text-slate-700">Chưa có banner nào</p>
            @if(has_permission('banners.create'))
            <a href="{{ route('admin.banners.create') }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Thêm banner</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th class="w-40">Ảnh</th><th>Tiêu đề</th><th>Liên kết</th><th>Kích hoạt</th><th class="text-right">Thao tác</th></tr>
            @foreach($banners as $b)
            <tr>
                <td>
                    @if($b->image_desktop)
                        <img src="{{ storage_url($b->image_desktop) }}" alt="" class="w-36 h-16 object-cover rounded-lg border">
                    @else
                        <div class="w-36 h-16 rounded-lg bg-slate-100 flex items-center justify-center text-xl">🖼️</div>
                    @endif
                </td>
                <td>
                    <div class="font-semibold">{{ $b->title }}</div>
                    <div class="text-xs text-slate-400">Thứ tự: {{ $b->sort_order }}</div>
                </td>
                <td class="text-sm text-slate-600 max-w-[220px] truncate">{{ $b->link ?? '—' }}</td>
                <td>
                    @if(has_permission('banners.publish'))
                    <form method="POST" action="{{ route('admin.banners.toggle-active', $b) }}" class="inline">
                        @csrf
                        <button title="Nhấn để bật/tắt" class="text-xs font-bold rounded-full px-2.5 py-1 {{ $b->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $b->is_active ? '✅ Đang hiện' : '⏸️ Đang tắt' }}
                        </button>
                    </form>
                    @else
                    <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $b->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $b->is_active ? '✅ Đang hiện' : '⏸️ Đang tắt' }}</span>
                    @endif
                </td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('banners.update'))
                        <a href="{{ route('admin.banners.edit', $b) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('banners.delete'))
                        <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" data-confirm="Xóa banner &quot;{{ $b->title }}&quot;?" class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $banners->links() }}</div>
    @endif
</div>
@endsection

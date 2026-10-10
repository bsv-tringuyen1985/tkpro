@extends('layouts.admin')

@section('title', 'Trang tĩnh')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Trang tĩnh</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">📄 Trang tĩnh</h1>
        <p class="text-sm text-slate-500 mt-1">Các trang nội dung cố định: giới thiệu, dịch vụ, liên hệ…</p>
    </div>
    @if(has_permission('pages.create'))
    <a href="{{ route('admin.pages.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm trang</a>
    @endif
</div>

<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tiêu đề trang…"
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
    @if($pages->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">📄</div>
            <p class="font-semibold text-slate-700">Chưa có trang nào</p>
            @if(has_permission('pages.create'))
            <a href="{{ route('admin.pages.create') }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Thêm trang</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th>Tiêu đề</th><th>Đường dẫn</th><th>Trạng thái</th><th>Cập nhật</th><th class="text-right">Thao tác</th></tr>
            @foreach($pages as $pg)
            <tr>
                <td class="font-semibold">{{ $pg->title }}</td>
                <td class="text-sm text-slate-500">/{{ $pg->slug }}</td>
                <td><span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $pg->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ status_label($pg->status) }}</span></td>
                <td class="text-sm text-slate-500 whitespace-nowrap">{{ $pg->updated_at->format('d/m/Y H:i') }}</td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('pages.update'))
                        <a href="{{ route('admin.pages.edit', $pg) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('pages.delete'))
                        <form method="POST" action="{{ route('admin.pages.destroy', $pg) }}" data-confirm="Xóa trang &quot;{{ $pg->title }}&quot;? Các mục menu đang trỏ tới trang này sẽ mất liên kết." class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $pages->links() }}</div>
    @endif
</div>
@endsection

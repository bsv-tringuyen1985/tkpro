@extends('layouts.admin')

@section('title', 'Hộp thư liên hệ')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Hộp thư liên hệ</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">✉️ Hộp thư liên hệ</h1>
        <p class="text-sm text-slate-500 mt-1">Tin nhắn khách hàng gửi từ form liên hệ trên website.</p>
    </div>
</div>

{{-- Tabs lọc --}}
<div class="flex gap-2 mb-5 text-sm">
    <a href="{{ route('admin.contact_messages.index') }}"
       class="rounded-lg px-4 py-2 font-semibold {{ !request('filter') ? 'bg-brand-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-400' }}">
        Tất cả
    </a>
    <a href="{{ route('admin.contact_messages.index', ['filter' => 'unread']) }}"
       class="rounded-lg px-4 py-2 font-semibold {{ request('filter') === 'unread' ? 'bg-brand-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-400' }}">
        Chưa đọc @if($unreadCount > 0)<span class="ml-1 bg-danger text-white text-xs rounded-full px-1.5">{{ $unreadCount }}</span>@endif
    </a>
    <a href="{{ route('admin.contact_messages.index', ['filter' => 'read']) }}"
       class="rounded-lg px-4 py-2 font-semibold {{ request('filter') === 'read' ? 'bg-brand-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-400' }}">
        Đã đọc
    </a>
    <form method="GET" class="ml-auto flex gap-2">
        @if(request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, SĐT…"
               class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍</button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($messages->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">📭</div>
            <p class="font-semibold text-slate-700">Không có tin nhắn nào</p>
            <p class="text-sm text-slate-500 mt-1">Tin nhắn mới từ website sẽ hiện ở đây.</p>
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th></th><th>Họ tên</th><th>Điện thoại</th><th>Tiêu đề</th><th>Thời gian</th><th class="text-right">Thao tác</th></tr>
            @foreach($messages as $m)
            <tr class="{{ $m->is_read ? '' : 'bg-amber-50/50' }}">
                <td class="w-8">
                    @if(!$m->is_read)<span class="inline-block w-2.5 h-2.5 rounded-full bg-danger" title="Chưa đọc"></span>@endif
                </td>
                <td class="{{ $m->is_read ? '' : 'font-bold' }}">
                    {{ $m->name }}
                    @if(!$m->is_read)<span class="ml-1 text-[.65rem] bg-danger text-white rounded-full px-1.5 py-0.5 font-bold">Mới</span>@endif
                </td>
                <td><a href="tel:{{ $m->phone }}" class="text-brand-700 hover:underline">{{ $m->phone }}</a></td>
                <td class="text-sm text-slate-600 max-w-[240px] truncate">{{ $m->subject ?? '—' }}</td>
                <td class="text-sm text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        <a href="{{ route('admin.contact_messages.show', $m) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">👁️ Xem</a>
                        @if(has_permission('contact_messages.delete'))
                        <form method="POST" action="{{ route('admin.contact_messages.destroy', $m) }}" data-confirm="Xóa tin nhắn của &quot;{{ $m->name }}&quot;?" class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $messages->links() }}</div>
    @endif
</div>
@endsection

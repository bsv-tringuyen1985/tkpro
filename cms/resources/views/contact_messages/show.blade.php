@extends('layouts.admin')

@section('title', 'Chi tiết tin nhắn')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.contact_messages.index') }}" class="hover:text-brand-700">Hộp thư liên hệ</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Chi tiết</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">✉️ Chi tiết tin nhắn</h1>
    <p class="text-sm text-slate-500 mb-6">Gửi lúc {{ $message->created_at->format('d/m/Y H:i') }}</p>

    <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
        <div class="grid sm:grid-cols-2 gap-5 mb-5">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Họ tên</p>
                <p class="font-semibold">{{ $message->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Điện thoại</p>
                <p><a href="tel:{{ $message->phone }}" class="font-semibold text-brand-700 hover:underline">{{ $message->phone }}</a></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Email</p>
                <p class="text-sm">{{ $message->email ? e($message->email) : '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Tiêu đề</p>
                <p class="text-sm">{{ $message->subject ?? '—' }}</p>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Nội dung tin nhắn</p>
            <div class="bg-slate-50 rounded-xl p-4 text-sm whitespace-pre-line leading-relaxed">{{ $message->message }}</div>
        </div>

        <div class="flex flex-wrap gap-2.5 mt-6">
            <a href="tel:{{ $message->phone }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">📞 Gọi lại</a>
            @if($message->email)
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject ?? 'Liên hệ TKPRO' }}" class="bg-white border border-brand-600 text-brand-700 hover:bg-brand-50 text-sm font-semibold rounded-lg px-5 py-2.5">📧 Gửi email</a>
            @endif
            @if(has_permission('contact_messages.update'))
            <form method="POST" action="{{ route('admin.contact_messages.mark-unread', $message) }}" class="inline">
                @csrf
                <button class="bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold rounded-lg px-5 py-2.5">↩️ Đánh dấu chưa đọc</button>
            </form>
            @endif
            @if(has_permission('contact_messages.delete'))
            <form method="POST" action="{{ route('admin.contact_messages.destroy', $message) }}" data-confirm="Xóa tin nhắn này?" class="inline">
                @csrf @method('DELETE')
                <button class="bg-white border border-red-300 text-danger hover:bg-red-50 text-sm font-semibold rounded-lg px-5 py-2.5">🗑️ Xóa</button>
            </form>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.contact_messages.index') }}" class="text-sm text-brand-700 hover:underline">← Quay lại hộp thư</a>
    </div>
</div>
@endsection

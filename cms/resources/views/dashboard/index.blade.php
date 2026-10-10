@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Tổng quan</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold">📊 Tổng quan</h1>
        <p class="text-sm text-slate-500 mt-1">Xin chào <strong>{{ auth()->user()->name }}</strong>! Đây là tình hình website hôm nay.</p>
    </div>
</div>

@if(auth()->user()->must_change_password)
<div class="mb-6 bg-amber-50 border border-amber-300 rounded-xl px-5 py-4 text-sm text-amber-900">
    ⚠️ Bạn đang dùng mật khẩu mặc định. Hãy <a href="{{ route('admin.password.change') }}" class="font-bold underline">đổi mật khẩu ngay</a> để bảo vệ website.
</div>
@endif

{{-- Thẻ thống kê --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-emerald-900/10 p-5 shadow-sm">
        <div class="text-3xl font-extrabold text-brand-700">{{ number_format($stats['products']) }}</div>
        <div class="text-sm text-slate-500 mt-1">📦 Sản phẩm</div>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-900/10 p-5 shadow-sm">
        <div class="text-3xl font-extrabold text-brand-700">{{ number_format($stats['product_categories']) }}</div>
        <div class="text-sm text-slate-500 mt-1">🗂️ Danh mục SP</div>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-900/10 p-5 shadow-sm">
        <div class="text-3xl font-extrabold text-brand-700">{{ number_format($stats['posts']) }}</div>
        <div class="text-sm text-slate-500 mt-1">📰 Bài viết</div>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-900/10 p-5 shadow-sm">
        <div class="text-3xl font-extrabold text-brand-700">{{ number_format($stats['pages']) }}</div>
        <div class="text-sm text-slate-500 mt-1">📄 Trang tĩnh</div>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-900/10 p-5 shadow-sm">
        <div class="text-3xl font-extrabold text-brand-700">{{ number_format($stats['users']) }}</div>
        <div class="text-sm text-slate-500 mt-1">👥 Người dùng</div>
    </div>
    <a href="{{ route('admin.contact_messages.index', ['filter' => 'unread']) }}" class="bg-white rounded-2xl border {{ $stats['unread_messages'] > 0 ? 'border-danger/40' : 'border-emerald-900/10' }} p-5 shadow-sm hover:shadow transition">
        <div class="text-3xl font-extrabold {{ $stats['unread_messages'] > 0 ? 'text-danger' : 'text-brand-700' }}">{{ number_format($stats['unread_messages']) }}</div>
        <div class="text-sm text-slate-500 mt-1">✉️ Tin nhắn chưa đọc</div>
    </a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Sản phẩm mới thêm --}}
    <div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold">🆕 Sản phẩm mới thêm</h2>
            @if(has_permission('products.view'))
            <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-700 hover:underline">Xem tất cả →</a>
            @endif
        </div>
        @if($latestProducts->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">Chưa có sản phẩm nào.</div>
        @else
        <div class="table-wrap overflow-x-auto">
            <table>
                <tr><th>Sản phẩm</th><th>Danh mục</th><th class="text-right">Giá</th></tr>
                @foreach($latestProducts as $p)
                <tr>
                    <td class="font-medium">{{ $p->name }}</td>
                    <td class="text-slate-500 text-sm">{{ $p->category->name ?? '—' }}</td>
                    <td class="text-right text-brand-700 font-semibold whitespace-nowrap">{{ format_price($p->price) }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
    </div>

    {{-- Tin nhắn chưa đọc --}}
    <div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold">✉️ Tin nhắn liên hệ chưa đọc</h2>
            @if(has_permission('contact_messages.view'))
            <a href="{{ route('admin.contact_messages.index') }}" class="text-sm text-brand-700 hover:underline">Xem hộp thư →</a>
            @endif
        </div>
        @if($unreadMessages->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">🎉 Không có tin nhắn nào chưa đọc.</div>
        @else
        <div class="table-wrap overflow-x-auto">
            <table>
                <tr><th>Họ tên</th><th>Điện thoại</th><th>Thời gian</th><th></th></tr>
                @foreach($unreadMessages as $m)
                <tr>
                    <td class="font-medium">{{ $m->name }} <span class="ml-1 text-[.65rem] bg-danger text-white rounded-full px-1.5 py-0.5 font-bold">Mới</span></td>
                    <td><a href="tel:{{ $m->phone }}" class="text-brand-700 hover:underline">{{ $m->phone }}</a></td>
                    <td class="text-slate-500 text-sm whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right"><a href="{{ route('admin.contact_messages.show', $m) }}" class="text-sm text-brand-700 hover:underline font-semibold">Xem</a></td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Nội dung mới cập nhật --}}
<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-bold">🔄 Nội dung mới cập nhật</h2>
    </div>
    @if($recentUpdates->isEmpty())
        <div class="p-8 text-center text-slate-400 text-sm">Chưa có nội dung nào.</div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th>Loại</th><th>Tiêu đề</th><th>Cập nhật lúc</th><th></th></tr>
            @foreach($recentUpdates as $item)
            <tr>
                <td><span class="text-xs bg-brand-50 text-brand-700 rounded-full px-2.5 py-1 font-semibold">{{ $item['type'] }}</span></td>
                <td class="font-medium">{{ $item['title'] }}</td>
                <td class="text-slate-500 text-sm whitespace-nowrap">{{ $item['updated_at']->format('d/m/Y H:i') }}</td>
                <td class="text-right"><a href="{{ $item['url'] }}" class="text-sm text-brand-700 hover:underline font-semibold">Sửa</a></td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif
</div>

{{-- Thao tác nhanh --}}
<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm p-5 mt-6">
    <h2 class="font-bold mb-3">⚡ Thao tác nhanh</h2>
    <div class="flex flex-wrap gap-3">
        @if(has_permission('products.create'))
        <a href="{{ route('admin.products.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm sản phẩm</a>
        @endif
        @if(has_permission('posts.create'))
        <a href="{{ route('admin.posts.create') }}" class="bg-white border border-brand-600 text-brand-700 hover:bg-brand-50 text-sm font-semibold rounded-lg px-4 py-2.5">➕ Đăng bài viết</a>
        @endif
        @if(has_permission('banners.create'))
        <a href="{{ route('admin.banners.create') }}" class="bg-white border border-brand-600 text-brand-700 hover:bg-brand-50 text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm banner</a>
        @endif
        @if(has_permission('media.create'))
        <a href="{{ route('admin.media.index') }}" class="bg-white border border-brand-600 text-brand-700 hover:bg-brand-50 text-sm font-semibold rounded-lg px-4 py-2.5">🖼️ Tải ảnh lên</a>
        @endif
    </div>
</div>
@endsection

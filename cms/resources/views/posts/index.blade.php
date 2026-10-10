@extends('layouts.admin')

@section('title', 'Bài viết')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Bài viết</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">📰 Bài viết</h1>
        <p class="text-sm text-slate-500 mt-1">Tin tức, khuyến mãi, hướng dẫn trên website.</p>
    </div>
    @if(has_permission('posts.create'))
    <a href="{{ route('admin.posts.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Đăng bài viết</a>
    @endif
</div>

<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tiêu đề bài viết…"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Chuyên mục</label>
        <select name="category_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">— Tất cả —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
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
    @if($posts->isEmpty())
        <div class="p-12 text-center">
            <div class="text-5xl mb-3">📰</div>
            <p class="font-semibold text-slate-700">Chưa có bài viết nào</p>
            @if(has_permission('posts.create'))
            <a href="{{ route('admin.posts.create') }}" class="inline-block mt-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5">➕ Đăng bài viết</a>
            @endif
        </div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th class="w-16">Ảnh</th><th>Tiêu đề</th><th>Chuyên mục</th><th>Tác giả</th><th>Trạng thái</th><th class="text-right">Thao tác</th></tr>
            @foreach($posts as $p)
            <tr>
                <td>
                    @if($p->featured_image)
                        <img src="{{ storage_url($p->featured_image) }}" alt="" class="w-12 h-12 object-cover rounded-lg border">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-xl">📰</div>
                    @endif
                </td>
                <td>
                    <div class="font-semibold">{{ $p->title }}</div>
                    <div class="text-xs text-slate-400">/{{ $p->slug }} · {{ $p->published_at ? $p->published_at->format('d/m/Y') : 'chưa xuất bản' }}</div>
                </td>
                <td class="text-sm text-slate-600">{{ $p->category->name ?? '—' }}</td>
                <td class="text-sm text-slate-600">{{ $p->author->name ?? '—' }}</td>
                <td>
                    @if(has_permission('posts.publish'))
                    <form method="POST" action="{{ route('admin.posts.toggle-status', $p) }}" class="inline">
                        @csrf
                        <button title="Nhấn để đổi trạng thái" class="text-xs font-bold rounded-full px-2.5 py-1 {{ $p->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ status_label($p->status) }}</button>
                    </form>
                    @else
                    <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $p->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ status_label($p->status) }}</span>
                    @endif
                </td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('posts.update'))
                        <a href="{{ route('admin.posts.edit', $p) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('posts.delete'))
                        <form method="POST" action="{{ route('admin.posts.destroy', $p) }}" data-confirm="Xóa bài viết &quot;{{ $p->title }}&quot;?" class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $posts->links() }}</div>
    @endif
</div>
@endsection

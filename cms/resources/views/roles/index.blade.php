@extends('layouts.admin')

@section('title', 'Vai trò & quyền')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Vai trò & quyền</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">🛡️ Vai trò & quyền</h1>
        <p class="text-sm text-slate-500 mt-1">Phân quyền chi tiết cho từng vai trò theo từng module.</p>
    </div>
    @if(has_permission('roles.create'))
    <a href="{{ route('admin.roles.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm vai trò</a>
    @endif
</div>

@if(!$hasPermissions)
<div class="bg-amber-50 border border-amber-300 rounded-xl px-5 py-4 text-sm text-amber-900 mb-5">
    ⚠️ Bảng <code>permissions</code> đang trống. Quyền được seed từ phía API — hãy chạy seed của API trước khi gán quyền cho vai trò.
</div>
@endif

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($roles as $role)
    <div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h2 class="font-bold text-lg">{{ $role->display_name }}</h2>
                <p class="text-xs text-slate-400">{{ $role->name }}{{ $role->isCore() ? ' · vai trò hệ thống' : '' }}</p>
            </div>
            <span class="bg-brand-50 text-brand-700 text-xs font-bold rounded-full px-2.5 py-1 whitespace-nowrap">👥 {{ $role->users_count }}</span>
        </div>
        <div class="flex flex-wrap gap-1.5 mb-4">
            @if($role->name === 'super-admin')
                <span class="text-xs bg-emerald-600 text-white rounded-full px-2.5 py-1 font-bold">♾️ Toàn quyền hệ thống</span>
            @else
                @forelse($role->permissions->take(8) as $perm)
                <span class="text-xs bg-slate-100 text-slate-600 rounded-full px-2.5 py-1">{{ $perm->name }}</span>
                @empty
                <span class="text-xs text-slate-400 italic">Chưa gán quyền nào</span>
                @endforelse
                @if($role->permissions->count() > 8)
                <span class="text-xs text-slate-400">+{{ $role->permissions->count() - 8 }} quyền nữa…</span>
                @endif
            @endif
        </div>
        <div class="flex gap-1.5">
            @if(has_permission('roles.update'))
            <a href="{{ route('admin.roles.edit', $role) }}" class="flex-1 text-center text-sm border border-slate-200 rounded-lg px-3 py-2 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa quyền</a>
            @endif
            @if(has_permission('roles.delete') && !$role->isCore())
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" data-confirm="Xóa vai trò &quot;{{ $role->display_name }}&quot;?" class="flex-1">
                @csrf @method('DELETE')
                <button class="w-full text-sm border border-red-200 rounded-lg px-3 py-2 hover:bg-red-50 text-danger font-semibold">🗑️ Xóa</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($roles->isEmpty())
<div class="bg-white rounded-2xl border border-emerald-900/10 p-12 text-center shadow-sm">
    <div class="text-5xl mb-3">🛡️</div>
    <p class="font-semibold text-slate-700">Chưa có vai trò nào</p>
</div>
@endif
@endsection

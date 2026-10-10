@extends('layouts.admin')

@section('title', 'Người dùng')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Người dùng</span>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">👥 Người dùng</h1>
        <p class="text-sm text-slate-500 mt-1">Quản lý tài khoản đăng nhập trang quản trị.</p>
    </div>
    @if(has_permission('users.create'))
    <a href="{{ route('admin.users.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5">➕ Thêm người dùng</a>
    @endif
</div>

<form method="GET" class="bg-white rounded-2xl border border-emerald-900/10 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Họ tên hoặc email…"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Vai trò</label>
        <select name="role" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">— Tất cả —</option>
            @foreach($roles as $r)
            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->display_name }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍 Lọc</button>
</form>

<div class="bg-white rounded-2xl border border-emerald-900/10 shadow-sm overflow-hidden">
    @if($users->isEmpty())
        <div class="p-12 text-center text-slate-400 text-sm">Không tìm thấy người dùng nào.</div>
    @else
    <div class="table-wrap overflow-x-auto">
        <table>
            <tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th class="text-right">Thao tác</th></tr>
            @foreach($users as $u)
            <tr>
                <td>
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm">{{ mb_substr($u->name, 0, 1) }}</span>
                        <div>
                            <div class="font-semibold">{{ $u->name }} @if($u->id === auth()->id())<span class="text-xs text-slate-400">(bạn)</span>@endif</div>
                            @if($u->must_change_password)<div class="text-xs text-amber-600 font-semibold">⚠️ Cần đổi mật khẩu</div>@endif
                        </div>
                    </div>
                </td>
                <td class="text-sm text-slate-600">{{ $u->email }}</td>
                <td>
                    @forelse($u->roles as $role)
                    <span class="inline-block text-xs bg-brand-50 text-brand-700 rounded-full px-2.5 py-1 font-semibold mr-1 mb-1">{{ $role->display_name }}</span>
                    @empty
                    <span class="text-xs text-slate-400">— chưa có vai trò —</span>
                    @endforelse
                </td>
                <td class="text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        @if(has_permission('users.update'))
                        <a href="{{ route('admin.users.edit', $u) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
                        @endif
                        @if(has_permission('users.delete') && $u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Xóa người dùng &quot;{{ $u->name }}&quot;?" class="inline">
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
    <div class="px-5 py-4 border-t border-slate-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection

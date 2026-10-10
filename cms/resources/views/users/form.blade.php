@extends('layouts.admin')

@section('title', $user ? 'Sửa người dùng' : 'Thêm người dùng')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.users.index') }}" class="hover:text-brand-700">Người dùng</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $user ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $user ? '✏️ Sửa người dùng' : '➕ Thêm người dùng' }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $user ? 'Cập nhật thông tin tài khoản "'.$user->name.'".' : 'Tạo tài khoản đăng nhập trang quản trị mới.' }}</p>

    <form method="POST" action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($user) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Họ tên <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Địa chỉ email <span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            <p class="text-xs text-slate-400 mt-1">Dùng email này để đăng nhập và nhận liên kết đặt lại mật khẩu.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold mb-1.5">Mật khẩu {{ $user ? '' : '*' }}</label>
                <input type="password" name="password" {{ $user ? '' : 'required' }} autocomplete="new-password" placeholder="{{ $user ? 'Để trống nếu không đổi' : 'Ít nhất 8 ký tự' }}"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">Nhập lại mật khẩu {{ $user ? '' : '*' }}</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Vai trò</label>
            <div class="grid sm:grid-cols-2 gap-2">
                @foreach($roles as $role)
                <label class="flex items-start gap-2.5 border border-slate-200 rounded-lg px-3.5 py-2.5 cursor-pointer hover:border-brand-400 text-sm">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           {{ in_array($role->id, old('roles', $user ? $user->roles->pluck('id')->toArray() : [])) ? 'checked' : '' }}
                           class="w-4 h-4 mt-0.5">
                    <span>
                        <span class="font-semibold">{{ $role->display_name }}</span>
                        <span class="block text-xs text-slate-400">{{ $role->name }}</span>
                    </span>
                </label>
                @endforeach
            </div>
            @if($roles->isEmpty())
            <p class="text-sm text-amber-600">⚠️ Chưa có vai trò nào trong hệ thống. Vai trò được seed từ phía API.</p>
            @endif
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="must_change_password" value="1" {{ old('must_change_password', $user->must_change_password ?? false) ? 'checked' : '' }} class="w-4 h-4">
                <span class="font-semibold">🔐 Yêu cầu đổi mật khẩu ở lần đăng nhập tiếp theo</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $user ? '💾 Lưu thay đổi' : '➕ Thêm người dùng' }}</button>
            <a href="{{ route('admin.users.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

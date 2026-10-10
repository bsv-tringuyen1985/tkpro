@extends('layouts.admin')

@section('title', 'Đổi mật khẩu')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Đổi mật khẩu</span>
@endsection

@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold mb-1">🔑 Đổi mật khẩu</h1>
    <p class="text-sm text-slate-500 mb-6">Mật khẩu mạnh giúp bảo vệ trang quản trị của bạn.</p>

    @if(auth()->user()->must_change_password)
    <div class="mb-5 bg-amber-50 border border-amber-300 text-amber-900 rounded-xl px-4 py-3 text-sm">
        ⚠️ <strong>Bạn đang dùng mật khẩu mặc định.</strong> Vì lý do bảo mật, hãy đổi sang mật khẩu mới ngay bây giờ.
    </div>
    @endif

    <form method="POST" action="{{ route('admin.password.change.update') }}" class="bg-white rounded-2xl shadow-sm border border-emerald-900/10 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="block text-sm font-semibold mb-1.5">Mật khẩu hiện tại <span class="text-danger">*</span></label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            @error('current_password')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold mb-1.5">Mật khẩu mới <span class="text-danger">*</span></label>
            <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Ít nhất 8 ký tự"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            @error('password')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold mb-1.5">Nhập lại mật khẩu mới <span class="text-danger">*</span></label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div class="flex gap-3 pt-1">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-6 py-2.5 text-sm">💾 Lưu mật khẩu mới</button>
            @if(!auth()->user()->must_change_password)
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-6 py-2.5 text-sm">Hủy</a>
            @endif
        </div>
    </form>
</div>
@endsection

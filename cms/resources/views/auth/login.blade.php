@extends('layouts.auth')

@section('title', 'Đăng nhập quản trị')
@section('heading', 'Đăng nhập quản trị')

@section('content')
<form method="POST" action="{{ route('admin.login') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Địa chỉ email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
               placeholder="admin@example.com"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Mật khẩu</label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
               placeholder="••••••••"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
    </div>

    <div class="flex items-center justify-between mb-5 text-sm">
        <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4">
            Ghi nhớ đăng nhập
        </label>
        <a href="{{ route('admin.password.request') }}" class="text-brand-700 hover:underline">Quên mật khẩu?</a>
    </div>

    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg py-2.5 transition">
        Đăng nhập →
    </button>
</form>
@endsection

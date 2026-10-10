@extends('layouts.auth')

@section('title', 'Quên mật khẩu')
@section('heading', 'Quên mật khẩu')
@section('subheading', 'Nhập email tài khoản để nhận liên kết đặt lại mật khẩu.')

@section('content')
<form method="POST" action="{{ route('admin.password.email') }}">
    @csrf

    <div class="mb-5">
        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Địa chỉ email</label>
        <input type="email" id="email" name="email" value="{{ old('email', request('email')) }}" required autofocus autocomplete="email"
               placeholder="admin@example.com"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
        <p class="text-xs text-slate-500 mt-1.5">Liên kết có hiệu lực trong 60 phút. Nếu mail chưa cấu hình SMTP, liên kết sẽ được ghi vào file log của hệ thống.</p>
    </div>

    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg py-2.5 transition">
        Gửi liên kết đặt lại mật khẩu
    </button>

    <p class="text-center mt-4 text-sm">
        <a href="{{ route('admin.login') }}" class="text-brand-700 hover:underline">← Quay lại đăng nhập</a>
    </p>
</form>
@endsection

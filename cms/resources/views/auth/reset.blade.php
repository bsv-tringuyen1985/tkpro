@extends('layouts.auth')

@section('title', 'Đặt lại mật khẩu')
@section('heading', 'Đặt lại mật khẩu')
@section('subheading', 'Nhập mật khẩu mới cho tài khoản của bạn.')

@section('content')
<form method="POST" action="{{ route('admin.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-4">
        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Địa chỉ email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Mật khẩu mới</label>
        <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Ít nhất 8 ký tự"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
    </div>

    <div class="mb-5">
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Nhập lại mật khẩu mới</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
    </div>

    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg py-2.5 transition">
        Đặt lại mật khẩu
    </button>
</form>
@endsection

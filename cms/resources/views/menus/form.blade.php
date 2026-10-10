@extends('layouts.admin')

@section('title', $menu ? 'Sửa menu' : 'Thêm menu')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.menus.index') }}" class="hover:text-brand-700">Menu</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $menu ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">{{ $menu ? '✏️ Sửa menu' : '➕ Thêm menu' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Menu là nhóm các liên kết điều hướng (VD: menu đầu trang, menu chân trang).</p>

    <form method="POST" action="{{ $menu ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm space-y-5">
        @csrf
        @if($menu) @method('PUT') @endif

        <div>
            <label class="block text-sm font-semibold mb-1.5">Tên menu <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $menu->name ?? '') }}" required placeholder="VD: Menu đầu trang"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $menu->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu để trống"
                   class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1.5">Vị trí hiển thị <span class="text-danger">*</span></label>
            <select name="location" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm">
                <option value="header" {{ old('location', $menu->location ?? 'header') === 'header' ? 'selected' : '' }}>Đầu trang (header)</option>
                <option value="footer" {{ old('location', $menu->location ?? '') === 'footer' ? 'selected' : '' }}>Chân trang (footer)</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $menu ? '💾 Lưu thay đổi' : '➕ Thêm menu' }}</button>
            <a href="{{ route('admin.menus.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', $role ? 'Sửa vai trò' : 'Thêm vai trò')

@section('breadcrumb')
<span class="text-slate-300">/</span><a href="{{ route('admin.roles.index') }}" class="hover:text-brand-700">Vai trò & quyền</a>
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">{{ $role ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-1">{{ $role ? '✏️ Sửa vai trò' : '➕ Thêm vai trò' }}</h1>
    <p class="text-sm text-slate-500 mb-6">Tick vào các ô để cấp quyền cho vai trò này.</p>

    <form method="POST" action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="space-y-6">
        @csrf
        @if($role) @method('PUT') @endif

        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Mã vai trò (slug) <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
                           {{ $role && $role->isCore() ? 'readonly' : '' }}
                           placeholder="VD: bien-tap-vien"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 disabled:bg-slate-100">
                    <p class="text-xs text-slate-400 mt-1">Chữ thường, số, gạch ngang. {{ $role && $role->isCore() ? 'Vai trò hệ thống không được đổi mã.' : '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Tên hiển thị <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" value="{{ old('display_name', $role->display_name ?? '') }}" required placeholder="VD: Biên tập viên"
                           class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
            </div>
        </div>

        @if($role && $role->name === 'super-admin')
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-sm text-emerald-900">
            ♾️ Vai trò <strong>Super Admin</strong> luôn có toàn quyền hệ thống — không cần (và không thể) gán quyền thủ công.
        </div>
        @elseif($groupedPermissions->isEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 text-sm text-amber-900">
            ⚠️ Bảng <code>permissions</code> đang trống. Quyền được seed từ phía API — hãy chạy seed của API trước.
        </div>
        @else
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">🔑 Phân quyền theo module</h2>
                <div class="flex gap-2 text-xs">
                    <button type="button" onclick="checkAll(true)" class="border border-slate-200 rounded-lg px-2.5 py-1.5 hover:bg-brand-50 font-semibold">Chọn tất cả</button>
                    <button type="button" onclick="checkAll(false)" class="border border-slate-200 rounded-lg px-2.5 py-1.5 hover:bg-slate-50 font-semibold">Bỏ chọn tất cả</button>
                </div>
            </div>

            <div class="space-y-5">
                @foreach($groupedPermissions as $group => $perms)
                <div class="border border-slate-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-brand-800">{{ module_label($group) }}</h3>
                        <button type="button" onclick="checkGroup('{{ $group }}', true)" class="text-xs text-brand-700 hover:underline font-semibold">Chọn cả nhóm</button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2" data-group="{{ $group }}">
                        @foreach($perms as $perm)
                        @php
                            $action = explode('.', $perm->name)[1] ?? $perm->name;
                        @endphp
                        <label class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 cursor-pointer hover:border-brand-400 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                   {{ in_array($perm->id, old('permissions', $selected)) ? 'checked' : '' }}
                                   class="w-4 h-4">
                            <span>{{ permission_action_label($action) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">{{ $role ? '💾 Lưu thay đổi' : '➕ Thêm vai trò' }}</button>
            <a href="{{ route('admin.roles.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg px-8 py-3 text-sm">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function checkAll(checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) { cb.checked = checked; });
}
function checkGroup(group, checked) {
    document.querySelectorAll('[data-group="' + group + '"] input[name="permissions[]"]').forEach(function (cb) { cb.checked = checked; });
}
</script>
@endpush

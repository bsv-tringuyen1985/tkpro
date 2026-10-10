<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Support\UserAccess;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.update')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
    }

    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('id')->get();
        $hasPermissions = Permission::count() > 0;

        return view('roles.index', compact('roles', 'hasPermissions'));
    }

    public function create()
    {
        return view('roles.form', [
            'role' => null,
            'groupedPermissions' => $this->groupedPermissions(),
            'selected' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        UserAccess::assertRolePermissions($request->user(), $data['permissions'] ?? []);

        $role = new Role();
        $role->name = $data['name'];
        $role->display_name = $data['display_name'] ?? null;
        $role->save();
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã thêm vai trò "'.$role->display_name.'".');
    }

    public function edit(Role $role)
    {
        return view('roles.form', [
            'role' => $role,
            'groupedPermissions' => $this->groupedPermissions(),
            'selected' => $role->permissions()->pluck('permissions.id')->toArray(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validateData($request, $role);
        UserAccess::assertRolePermissions($request->user(), $data['permissions'] ?? [], $role);

        // Không cho đổi slug name của vai trò lõi.
        if (! $role->isCore()) {
            $role->name = $data['name'];
        }
        $role->display_name = $data['display_name'] ?? null;
        $role->save();

        // Vai trò super-admin luôn có toàn quyền — không cho sửa quyền của nó
        // để tránh tự khóa mình.
        if ($role->name !== 'super-admin') {
            $role->permissions()->sync($data['permissions'] ?? []);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã cập nhật vai trò "'.$role->display_name.'".');
    }

    public function destroy(Role $role)
    {
        UserAccess::assertRolePermissions(auth()->user(), [], $role);
        if ($role->isCore()) {
            return back()->with('error', 'Không thể xóa vai trò hệ thống "'.$role->display_name.'".');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Không thể xóa vai trò "'.$role->display_name.'" vì vẫn còn người dùng thuộc vai trò này.');
        }

        $name = $role->display_name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã xóa vai trò "'.$name.'".');
    }

    protected function validateData(Request $request, ?Role $role = null): array
    {
        if ($role?->isCore()) {
            $request->merge(['name' => $role->name]);
        }
        return $request->validate([
            'name' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:roles,name'.($role ? ','.$role->id : ''),
            'display_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Vui lòng nhập mã vai trò (slug).',
            'name.regex' => 'Mã vai trò chỉ gồm chữ thường, số và gạch ngang.',
            'name.unique' => 'Mã vai trò này đã tồn tại.',
            'display_name.required' => 'Vui lòng nhập tên hiển thị của vai trò.',
            'permissions.*.exists' => 'Có quyền không tồn tại trong danh sách đã chọn.',
        ]);
    }

    /**
     * Quyền nhóm theo module: ['products' => Collection(...)].
     */
    protected function groupedPermissions()
    {
        return Permission::orderBy('group')->orderBy('id')->get()->groupBy('group');
    }
}

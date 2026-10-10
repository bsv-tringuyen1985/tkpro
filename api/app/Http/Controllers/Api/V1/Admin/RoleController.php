<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Traits\ApiResponse;
use App\Models\Permission;
use App\Models\Role;
use App\Support\UserAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('id')->get();

        return $this->success(RoleResource::collection($roles));
    }

    /** Danh sách tất cả quyền, nhóm theo module — dùng khi gán quyền cho vai trò. */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::orderBy('group')->orderBy('id')->get()->groupBy('group');

        $result = [];
        foreach ($permissions as $group => $items) {
            $result[] = [
                'group' => $group,
                'permissions' => PermissionResource::collection($items),
            ];
        }

        return $this->success($result);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        UserAccess::assertRolePermissions($request->user(), (array) $request->input('permissions', []));
        $role = Role::create($request->safe()->except(['permissions']));

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->input('permissions'));
        }

        $role->load('permissions');

        return $this->success(new RoleResource($role), 'Tạo vai trò thành công.', 201);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions')->loadCount('users');

        return $this->success(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        UserAccess::assertRolePermissions($request->user(), (array) $request->input('permissions', []), $role);
        if ($role->isSystem() && $request->has('name') && $request->input('name') !== $role->name) {
            return $this->error('Không thể đổi mã của vai trò hệ thống.', 422);
        }

        $role->update($request->safe()->except(['permissions']));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        $role->load('permissions');

        return $this->success(new RoleResource($role), 'Cập nhật vai trò thành công.');
    }

    public function destroy(Role $role): JsonResponse
    {
        UserAccess::assertRolePermissions(auth()->user(), [], $role);
        if ($role->isSystem()) {
            return $this->error('Không thể xóa vai trò hệ thống.', 422);
        }
        if ($role->users()->exists()) {
            return $this->error('Không thể xóa vai trò đang gán cho người dùng.', 422);
        }

        $role->delete();

        return $this->success(null, 'Xóa vai trò thành công.');
    }
}

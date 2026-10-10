<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\Role;
use App\Support\UserAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(UserResource::collection($query->paginate($perPage)->withQueryString()));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['roles']);
        $user = DB::transaction(function () use ($request, $data) {
            $roles = (array) $request->input('roles', []);
            UserAccess::assertRoles($request->user(), $roles);
            $user = User::create($data);
            $user->roles()->sync($roles);
            return $user;
        });

        $user->load('roles.permissions');

        return $this->success(new UserResource($user), 'Tạo người dùng thành công.', 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles.permissions');

        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->safe()->except(['roles']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($request, $user, $data) {
            Role::where('name', 'super-admin')->lockForUpdate()->first();
            UserAccess::assertManage($request->user(), $user);
            if ($request->has('roles')) {
                UserAccess::assertRoles($request->user(), (array) $request->input('roles', []), $user);
            }
            $user->update($data);
            if ($request->has('roles')) {
                $user->roles()->sync((array) $request->input('roles', []));
            }
            if (isset($data['password'])) {
                $user->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
                $user->tokens()->delete();
            }
        });

        $user->load('roles.permissions');

        return $this->success(new UserResource($user), 'Cập nhật người dùng thành công.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        DB::transaction(function () use ($request, $user) {
            Role::where('name', 'super-admin')->lockForUpdate()->first();
            UserAccess::assertDelete($request->user(), $user);
            $user->tokens()->delete();
            $user->delete();
        });

        return $this->success(null, 'Xóa người dùng thành công.');
    }
}

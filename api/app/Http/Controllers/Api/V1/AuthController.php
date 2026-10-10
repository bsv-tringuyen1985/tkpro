<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    /** Đăng nhập, trả về Sanctum bearer token. Rate limit: 5/phút. */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->error('Email hoặc mật khẩu không đúng.', 401);
        }

        $user->load('roles.permissions');
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'must_change_password' => (bool) $user->must_change_password,
            'user' => new UserResource($user),
        ], 'Đăng nhập thành công.');
    }

    /** Đăng xuất: thu hồi token hiện tại. */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        return $this->success(null, 'Đăng xuất thành công.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'different:current_password'],
        ]);
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return $this->error('Mật khẩu hiện tại không đúng.', 422, [
                'current_password' => ['Mật khẩu hiện tại không đúng.'],
            ]);
        }
        DB::transaction(function () use ($user, $data) {
            $user->forceFill([
                'password' => Hash::make($data['password']),
                'must_change_password' => false,
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
        });

        return $this->success(null, 'Đã đổi mật khẩu. Vui lòng đăng nhập lại.');
    }

    /** Thông tin user đang đăng nhập kèm vai trò và quyền. */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');

        return $this->success(new UserResource($user), 'Thành công.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đổi mật khẩu trước khi sử dụng chức năng quản trị.',
                'must_change_password' => true,
            ], 403);
        }

        return $next($request);
    }
}

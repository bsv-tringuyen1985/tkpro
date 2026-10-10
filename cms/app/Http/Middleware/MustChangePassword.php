<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ép đổi mật khẩu khi users.must_change_password = true.
 * Chuyển hướng tới trang đổi mật khẩu, trừ chính trang đổi mật khẩu
 * và đăng xuất.
 */
class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $onPasswordRoutes = $request->routeIs('admin.password.change')
                || $request->routeIs('admin.password.change.update');

            $onLogout = $request->routeIs('admin.logout');

            if (! $onPasswordRoutes && ! $onLogout) {
                return redirect()
                    ->route('admin.password.change')
                    ->with('warning', 'Vì lý do bảo mật, bạn cần đổi mật khẩu mặc định trước khi tiếp tục sử dụng.');
            }
        }

        return $next($request);
    }
}

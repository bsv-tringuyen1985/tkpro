<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\UserAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.update')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('name');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($sub) => $sub->where('roles.name', $request->get('role')));
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('display_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = $this->assignableRoles();

        return view('users.form', ['user' => null, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'must_change_password' => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Email này đã được dùng cho tài khoản khác.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'roles.*.exists' => 'Vai trò đã chọn không tồn tại.',
        ]);

        $user = DB::transaction(function () use ($request, $data) {
            UserAccess::assertRoles($request->user(), $data['roles'] ?? []);
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'must_change_password' => $request->boolean('must_change_password'),
            ]);
            $user->roles()->sync($data['roles'] ?? []);
            return $user;
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã thêm người dùng "'.$user->name.'".');
    }

    public function edit(User $user)
    {
        UserAccess::assertManage(auth()->user(), $user);
        $roles = $this->assignableRoles();

        return view('users.form', ['user' => $user, 'roles' => $roles]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'must_change_password' => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Email này đã được dùng cho tài khoản khác.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'roles.*.exists' => 'Vai trò đã chọn không tồn tại.',
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            Role::where('name', 'super-admin')->lockForUpdate()->first();
            UserAccess::assertManage($request->user(), $user);
            UserAccess::assertRoles($request->user(), $data['roles'] ?? [], $user);
            $user->name = $data['name'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->revokeApiTokens();
            }
            $user->must_change_password = $request->boolean('must_change_password');
            $user->save();
            $user->roles()->sync($data['roles'] ?? []);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã cập nhật người dùng "'.$user->name.'".');
    }

    public function destroy(Request $request, User $user)
    {
        $name = $user->name;
        DB::transaction(function () use ($request, $user) {
            Role::where('name', 'super-admin')->lockForUpdate()->first();
            UserAccess::assertDelete($request->user(), $user);
            $user->revokeApiTokens();
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa người dùng "'.$name.'".');
    }

    private function assignableRoles()
    {
        return Role::with('permissions')->orderBy('display_name')->get()
            ->filter(fn ($role) => UserAccess::canAssignRole(auth()->user(), $role));
    }
}

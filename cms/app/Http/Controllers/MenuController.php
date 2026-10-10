<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menus.view')->only(['index', 'show']);
        $this->middleware('permission:menus.create')->only(['create', 'store']);
        $this->middleware('permission:menus.update')->only(['edit', 'update']);
        $this->middleware('permission:menus.delete')->only(['destroy']);
    }

    /**
     * Liệt kê các menu (header/footer) kèm cây mục con.
     */
    public function index()
    {
        $menus = Menu::with(['rootItems.children' => function ($query) {
            $query->where('is_active', true);
        }])->orderBy('id')->get();

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.form', ['menu' => null]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:menus,slug',
            'location' => 'required|in:header,footer',
        ], [
            'name.required' => 'Vui lòng nhập tên menu.',
            'slug.unique' => 'Slug này đã được dùng.',
            'location.required' => 'Vui lòng chọn vị trí hiển thị.',
            'location.in' => 'Vị trí không hợp lệ.',
        ]);

        $menu = new Menu();
        $menu->name = $data['name'];
        $menu->location = $data['location'];
        $menu->slug = $data['slug'] ?: \App\Support\SlugGenerator::unique(Menu::class, $data['name']);
        $menu->save();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Đã thêm menu "'.$menu->name.'".');
    }

    public function edit(Menu $menu)
    {
        return view('menus.form', ['menu' => $menu]);
    }

    public function update(\Illuminate\Http\Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:menus,slug,'.$menu->id,
            'location' => 'required|in:header,footer',
        ], [
            'name.required' => 'Vui lòng nhập tên menu.',
            'slug.unique' => 'Slug này đã được dùng.',
            'location.required' => 'Vui lòng chọn vị trí hiển thị.',
            'location.in' => 'Vị trí không hợp lệ.',
        ]);

        $menu->name = $data['name'];
        $menu->location = $data['location'];
        $menu->slug = $data['slug'] ?: \App\Support\SlugGenerator::unique(Menu::class, $data['name'], $menu->id);
        $menu->save();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Đã cập nhật menu "'.$menu->name.'".');
    }

    public function destroy(Menu $menu)
    {
        $name = $menu->name;
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Đã xóa menu "'.$name.'" cùng toàn bộ mục con.');
    }
}

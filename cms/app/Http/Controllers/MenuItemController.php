<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menus.view')->only(['index']);
        $this->middleware('permission:menus.create')->only(['create', 'store']);
        $this->middleware('permission:menus.update')->only(['edit', 'update']);
        $this->middleware('permission:menus.delete')->only(['destroy']);
    }

    /**
     * Quản lý các mục của 1 menu, hiển thị dạng cây cha — con.
     */
    public function index(Menu $menu)
    {
        $menu->load(['rootItems.children', 'rootItems.page']);

        return view('menus.items', compact('menu'));
    }

    public function create(Menu $menu)
    {
        return view('menus.item-form', [
            'menu' => $menu,
            'item' => null,
            'parents' => $this->parentOptions($menu),
            'pages' => Page::where('status', 'published')->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function store(Request $request, Menu $menu)
    {
        $data = $this->validateData($request, $menu);

        $menu->items()->create($data);

        return redirect()->route('admin.menu_items.index', $menu)
            ->with('success', 'Đã thêm mục menu "'.$data['title'].'".');
    }

    public function edit(Menu $menu, MenuItem $menuItem)
    {
        $this->ensureBelongsToMenu($menu, $menuItem);

        return view('menus.item-form', [
            'menu' => $menu,
            'item' => $menuItem,
            'parents' => $this->parentOptions($menu, $menuItem->id),
            'pages' => Page::where('status', 'published')->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function update(Request $request, Menu $menu, MenuItem $menuItem)
    {
        $this->ensureBelongsToMenu($menu, $menuItem);

        $data = $this->validateData($request, $menu, $menuItem);
        $menuItem->update($data);

        return redirect()->route('admin.menu_items.index', $menu)
            ->with('success', 'Đã cập nhật mục menu "'.$menuItem->title.'".');
    }

    public function destroy(Menu $menu, MenuItem $menuItem)
    {
        $this->ensureBelongsToMenu($menu, $menuItem);

        $title = $menuItem->title;
        $menuItem->delete();

        return redirect()->route('admin.menu_items.index', $menu)
            ->with('success', 'Đã xóa mục menu "'.$title.'" cùng các mục con.');
    }

    protected function validateData(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'type' => 'required|in:url,page',
            'url' => 'nullable|string|max:500',
            'page_id' => 'nullable|exists:pages,id',
            'target' => 'nullable|in:_self,_blank',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề mục menu.',
            'parent_id.exists' => 'Mục cha không tồn tại.',
            'type.required' => 'Vui lòng chọn loại liên kết.',
            'type.in' => 'Loại liên kết không hợp lệ.',
            'page_id.exists' => 'Trang đã chọn không tồn tại.',
        ]);

        // Mục cha phải thuộc cùng menu và không được là chính nó.
        if (! empty($data['parent_id'])) {
            $parent = MenuItem::find($data['parent_id']);
            if (! $parent || $parent->menu_id !== $menu->id) {
                abort(422, 'Mục cha phải thuộc cùng menu này.');
            }
            if ($item && (int) $data['parent_id'] === $item->id) {
                abort(422, 'Mục cha không được là chính mục này.');
            }
        }

        if ($data['type'] === 'page' && empty($data['page_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'page_id' => 'Vui lòng chọn trang liên kết.',
            ]);
        }

        if ($data['type'] === 'url' && trim((string) ($data['url'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'url' => 'Vui lòng nhập đường dẫn URL.',
            ]);
        }

        return [
            'title' => $data['title'],
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'],
            'url' => $data['type'] === 'url' ? $data['url'] : null,
            'page_id' => $data['type'] === 'page' ? $data['page_id'] : null,
            'target' => $data['target'] ?? '_self',
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function parentOptions(Menu $menu, ?int $excludeId = null): array
    {
        $query = $menu->items()->whereNull('parent_id');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('sort_order')->pluck('title', 'id')->toArray();
    }

    protected function ensureBelongsToMenu(Menu $menu, MenuItem $menuItem): void
    {
        if ($menuItem->menu_id !== $menu->id) {
            abort(404);
        }
    }
}

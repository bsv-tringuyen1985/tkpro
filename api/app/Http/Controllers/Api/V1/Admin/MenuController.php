<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\SlugHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\MenuResource;
use App\Http\Traits\ApiResponse;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Menu::query();

        if ($request->filled('location')) {
            $query->where('location', $request->string('location')->toString());
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(MenuResource::collection($query->paginate($perPage)->withQueryString()));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = SlugHelper::unique(Menu::class, $data['slug'] ?? SlugHelper::make($data['name']));
        $menu = Menu::create($data);

        return $this->success(new MenuResource($menu), 'Tạo menu thành công.', 201);
    }

    /** Chi tiết menu kèm cây mục menu đầy đủ. */
    public function show(Menu $menu): JsonResponse
    {
        $menu->setRelation('items', $this->tree($menu));

        return $this->success(new MenuResource($menu));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validated();
        $slugInput = $data['slug'] ?? null;
        unset($data['slug']);
        if (is_string($slugInput) && $slugInput !== '') {
            $data['slug'] = SlugHelper::unique(Menu::class, $slugInput, $menu->id);
        } elseif (isset($data['name']) && $data['name'] !== $menu->name) {
            $data['slug'] = SlugHelper::unique(Menu::class, SlugHelper::make($data['name']), $menu->id);
        }

        $menu->update($data);

        return $this->success(new MenuResource($menu), 'Cập nhật menu thành công.');
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return $this->success(null, 'Xóa menu thành công.');
    }

    /** Danh sách mục menu của một menu (dạng cây). */
    public function items(Menu $menu): JsonResponse
    {
        return $this->success(MenuItemResource::collection($this->tree($menu)));
    }

    public function storeItem(MenuItemRequest $request, Menu $menu): JsonResponse
    {
        $item = $menu->items()->create($request->validated());
        $item->load('page');

        return $this->success(new MenuItemResource($item), 'Thêm mục menu thành công.', 201);
    }

    public function updateItem(MenuItemRequest $request, MenuItem $item): JsonResponse
    {
        $item->update($request->validated());
        $item->load('page');

        return $this->success(new MenuItemResource($item), 'Cập nhật mục menu thành công.');
    }

    public function destroyItem(MenuItem $item): JsonResponse
    {
        $item->delete();

        return $this->success(null, 'Xóa mục menu thành công.');
    }

    /** Dựng cây mục menu (kèm page) cho một menu. */
    protected function tree(Menu $menu)
    {
        $items = MenuItem::where('menu_id', $menu->id)
            ->with('page')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return MenuItem::buildTree($items);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Http\Traits\ApiResponse;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    use ApiResponse;

    /** Menu theo vị trí (header|footer), trả về cây mục menu đang bật. */
    public function show(string $location): JsonResponse
    {
        if (! in_array($location, ['header', 'footer'], true)) {
            return $this->notFound('Không tìm thấy menu.');
        }

        $menu = Menu::where('location', $location)->first();

        if (! $menu) {
            return $this->notFound('Không tìm thấy menu.');
        }

        $items = MenuItem::where('menu_id', $menu->id)
            ->where('is_active', true)
            ->with('page')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menu->setRelation('items', MenuItem::buildTree($items));

        return $this->success(new MenuResource($menu));
    }
}

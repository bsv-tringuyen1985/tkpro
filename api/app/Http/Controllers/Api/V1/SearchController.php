<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponse;

    /** Tìm kiếm gộp sản phẩm + bài viết đã xuất bản. */
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString();

        if (mb_strlen($q) < 2) {
            return $this->error('Vui lòng nhập từ khóa tìm kiếm (ít nhất 2 ký tự).', 422);
        }

        $products = Product::published()
            ->with(['category', 'images'])
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            })
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $posts = Post::published()
            ->with(['category'])
            ->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%");
            })
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        return $this->success([
            'products' => ProductResource::collection($products),
            'posts' => PostResource::collection($posts),
        ]);
    }
}

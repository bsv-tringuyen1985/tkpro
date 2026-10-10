<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Traits\ApiResponse;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    use ApiResponse;

    public function show(string $slug): JsonResponse
    {
        $page = Page::published()->with('seoMeta')->where('slug', $slug)->first();

        if (! $page) {
            return $this->notFound('Không tìm thấy trang.');
        }

        return $this->success(new PageResource($page));
    }
}

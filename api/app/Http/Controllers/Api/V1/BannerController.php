<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Traits\ApiResponse;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    use ApiResponse;

    /** Chỉ banner đang bật, sắp xếp theo sort_order. */
    public function index(): JsonResponse
    {
        $banners = Banner::active()->orderBy('sort_order')->orderBy('id')->get();

        return $this->success(BannerResource::collection($banners));
    }
}

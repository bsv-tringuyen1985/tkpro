<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Http\Traits\ApiResponse;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Banner::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $query->orderBy('sort_order')->orderBy('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(BannerResource::collection($query->paginate($perPage)->withQueryString()));
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $banner = Banner::create($request->validated());

        return $this->success(new BannerResource($banner), 'Tạo banner thành công.', 201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return $this->success(new BannerResource($banner));
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $banner->update($request->validated());

        return $this->success(new BannerResource($banner), 'Cập nhật banner thành công.');
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return $this->success(null, 'Xóa banner thành công.');
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\Concerns\ResolvesSlug;
use App\Http\Controllers\Api\V1\Admin\Concerns\SavesSeoMeta;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Http\Traits\ApiResponse;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use ApiResponse, ResolvesSlug, SavesSeoMeta;

    public function index(Request $request): JsonResponse
    {
        $query = Page::query();

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('title', 'like', "%{$q}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $query->orderBy('sort_order')->orderBy('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(PageResource::collection($query->paginate($perPage)->withQueryString()));
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $data = $this->resolveSlug(Page::class, $request->safe()->except(['seo']), null, 'title');
        $page = Page::create($data);
        $this->saveSeoMeta($page, $request->input('seo'));
        $page->load('seoMeta');

        return $this->success(new PageResource($page), 'Tạo trang thành công.', 201);
    }

    public function show(Page $page): JsonResponse
    {
        $page->load('seoMeta');

        return $this->success(new PageResource($page));
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $data = $this->resolveSlug(Page::class, $request->safe()->except(['seo']), $page, 'title');
        $page->update($data);
        $this->saveSeoMeta($page, $request->input('seo'));
        $page->load('seoMeta');

        return $this->success(new PageResource($page), 'Cập nhật trang thành công.');
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return $this->success(null, 'Xóa trang thành công.');
    }
}

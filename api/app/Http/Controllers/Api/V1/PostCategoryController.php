<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;

class PostCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = PostCategory::published()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->success(PostCategoryResource::collection($categories));
    }
}

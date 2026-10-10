<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    /** Chỉ các nhóm public: general, contact, social, seo. */
    public function index(): JsonResponse
    {
        return $this->success(Setting::publicAll());
    }
}

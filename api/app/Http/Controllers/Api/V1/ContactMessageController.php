<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Http\Resources\ContactMessageResource;
use App\Http\Traits\ApiResponse;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    use ApiResponse;

    /** Form liên hệ public. Rate limit: 10/phút (throttle:contact ở route). */
    public function store(ContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::create($request->validated());

        return $this->success(
            new ContactMessageResource($message),
            'Cảm ơn bạn đã liên hệ! TKPRO sẽ phản hồi trong thời gian sớm nhất.',
            201
        );
    }
}

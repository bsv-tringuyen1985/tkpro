<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactMessageUpdateRequest;
use App\Http\Resources\ContactMessageResource;
use App\Http\Traits\ApiResponse;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::query();

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }
        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $this->paginated(ContactMessageResource::collection($query->paginate($perPage)->withQueryString()));
    }

    /** Đánh dấu đã đọc / chưa đọc tin nhắn liên hệ. */
    public function update(ContactMessageUpdateRequest $request, ContactMessage $message): JsonResponse
    {
        $isRead = $request->input('is_read', true);

        if ($isRead) {
            $message->markAsRead();
        } else {
            $message->markAsUnread();
        }

        return $this->success(
            new ContactMessageResource($message->fresh()),
            $isRead ? 'Đã đánh dấu tin nhắn là đã đọc.' : 'Đã đánh dấu tin nhắn là chưa đọc.'
        );
    }

    public function destroy(ContactMessage $message): JsonResponse
    {
        $message->delete();

        return $this->success(null, 'Xóa tin nhắn thành công.');
    }
}

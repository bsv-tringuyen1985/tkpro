<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Envelope response thong nhat theo contract 3.1:
 * thanh cong: {success:true, message, data, meta?}
 * loi:        {success:false, message, errors?}
 */
trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Thành công', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Tra ve Resource Collection co phan trang theo envelope cua contract:
     * {success, message, data:[...], meta:{current_page, per_page, total, last_page}}.
     */
    protected function paginated(AnonymousResourceCollection $collection, string $message = 'Thành công'): JsonResponse
    {
        $array = $collection->response()->getData(true);
        $meta = $array['meta'] ?? [];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $array['data'] ?? [],
            'meta' => [
                'current_page' => $meta['current_page'] ?? 1,
                'per_page' => $meta['per_page'] ?? 15,
                'total' => $meta['total'] ?? 0,
                'last_page' => $meta['last_page'] ?? 1,
            ],
        ]);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function notFound(string $message = 'Không tìm thấy dữ liệu.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function forbidden(string $message = 'Bạn không có quyền thực hiện thao tác này.'): JsonResponse
    {
        return $this->error($message, 403);
    }
}

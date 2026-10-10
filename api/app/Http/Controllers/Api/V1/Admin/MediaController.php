<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Http\Resources\MediaResource;
use App\Http\Traits\ApiResponse;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Media::with('createdBy');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('filename', 'like', "%{$q}%")
                    ->orWhere('alt', 'like', "%{$q}%");
            });
        }
        if ($request->filled('mime')) {
            $query->where('mime_type', 'like', $request->string('mime')->toString().'%');
        }

        $query->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 24), 1), 100);

        return $this->paginated(MediaResource::collection($query->paginate($perPage)->withQueryString()));
    }

    /**
     * Upload file ảnh: chỉ jpg/jpeg/png/webp/svg, ≤ 5MB,
     * kiểm tra kích thước ảnh (tối đa 6000px mỗi chiều, trừ SVG),
     * tên file ngẫu nhiên, lưu tại storage/app/public/media/YYYY/MM/.
     */
    public function upload(MediaUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $mime = (string) $file->getMimeType();
        $maxDimension = (int) env('MEDIA_MAX_DIMENSION', 6000);

        // Kiểm tra kích thước ảnh bằng getimagesize (bỏ qua với SVG).
        $width = null;
        $height = null;
        if ($mime !== 'image/svg+xml') {
            $info = @getimagesize($file->getRealPath());
            if ($info === false) {
                return $this->error('File ảnh không đọc được, vui lòng thử file khác.', 422);
            }
            [$width, $height] = $info;
            if ($width > $maxDimension || $height > $maxDimension) {
                return $this->error("Kích thước ảnh tối đa {$maxDimension}px mỗi chiều.", 422);
            }
        }

        $directory = 'media/'.date('Y').'/'.date('m');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = Str::random(32).'.'.$extension;

        $path = $file->storeAs($directory, $filename, 'public');
        if ($path === false) {
            return $this->error('Tải file lên thất bại, vui lòng thử lại.', 500);
        }

        $media = Media::create([
            'filename' => $filename,
            'path' => $path,
            'url' => '/storage/'.$path,
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => $request->input('alt'),
            'disk' => 'public',
            'created_by' => $request->user()->id,
        ]);

        return $this->success(new MediaResource($media), 'Tải lên thành công.', 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return $this->success(null, 'Xóa file thành công.');
    }
}

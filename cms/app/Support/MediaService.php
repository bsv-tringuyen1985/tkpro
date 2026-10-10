<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Xử lý upload ảnh dùng chung cho CMS.
 * File lưu vào disk 'public' (storage/app/public/...), DB bảng media
 * lưu path/url/filename/mime_type/size/width/height/alt — dùng chung với API.
 */
class MediaService
{
    /**
     * Lưu 1 file upload vào disk public + tạo bản ghi media.
     *
     * @throws \InvalidArgumentException khi file không hợp lệ
     */
    public static function storeUpload(UploadedFile $file, string $folder = 'media'): Media
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException('Tập tin tải lên không hợp lệ.');
        }

        $path = $file->store($folder.'/'.date('Y/m'), 'public');

        $media = new Media();
        $media->filename = $file->getClientOriginalName();
        $media->path = $path;
        $media->url = '/storage/'.$path;
        $media->mime_type = $file->getMimeType() ?: 'application/octet-stream';
        $media->size = (int) $file->getSize();
        $media->disk = 'public';
        $media->alt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if (str_starts_with($media->mime_type, 'image/') && $media->mime_type !== 'image/svg+xml') {
            $info = @getimagesize($file->getRealPath());
            if ($info !== false) {
                $media->width = $info[0];
                $media->height = $info[1];
            }
        }

        $media->created_by = auth()->id();
        $media->save();

        return $media;
    }

    /**
     * Xóa bản ghi media kèm file vật lý trên disk.
     */
    public static function delete(Media $media): void
    {
        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
    }

    /**
     * Rule validate dùng chung cho ảnh upload: jpg/jpeg/png/webp/svg, tối đa 5MB.
     */
    public static function imageRules(bool $required = false): string
    {
        return ($required ? 'required' : 'nullable').'|file|mimes:jpg,jpeg,png,webp,svg|max:5120';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Support\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:media.view')->only(['index']);
        $this->middleware('permission:media.create')->only(['store']);
        $this->middleware('permission:media.delete')->only(['destroy']);
    }

    /**
     * Thư viện ảnh: lưới, tìm kiếm, lọc loại file, phân trang.
     * Thêm ?picker=1 để mở ở chế độ chọn ảnh (dùng trong modal của form).
     */
    public function index(Request $request)
    {
        $query = Media::with('creator')->orderByDesc('created_at');

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('filename', 'like', '%'.$q.'%')
                    ->orWhere('alt', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('type')) {
            if ($request->get('type') === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($request->get('type') === 'other') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $media = $query->paginate(24)->withQueryString();
        $picker = $request->boolean('picker');

        if ($picker) {
            return view('media.picker', compact('media'));
        }

        return view('media.index', compact('media'));
    }

    /**
     * Upload nhiều ảnh cùng lúc (tối đa 10 file/lần).
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => MediaService::imageRules(true),
        ], [
            'files.required' => 'Vui lòng chọn ít nhất 1 ảnh để tải lên.',
            'files.max' => 'Mỗi lần chỉ tải lên tối đa 10 ảnh.',
            'files.*.required' => 'Có tập tin trống, vui lòng chọn lại.',
            'files.*.mimes' => 'Chỉ chấp nhận ảnh jpg, jpeg, png, webp, svg.',
            'files.*.max' => 'Mỗi ảnh không được quá 5MB.',
        ]);

        $count = 0;
        foreach ($request->file('files', []) as $file) {
            if ($file && $file->isValid()) {
                MediaService::storeUpload($file);
                $count++;
            }
        }

        return back()->with('success', 'Đã tải lên '.$count.' ảnh vào thư viện.');
    }

    public function destroy(Media $media)
    {
        // Không cho xóa ảnh đang được dùng làm ảnh đại diện ở nơi khác thì vẫn
        // cho phép xóa file — chỉ cảnh báo nhẹ qua thông báo.
        $filename = $media->filename;
        MediaService::delete($media);

        return back()->with('success', 'Đã xóa ảnh "'.$filename.'" khỏi thư viện.');
    }
}

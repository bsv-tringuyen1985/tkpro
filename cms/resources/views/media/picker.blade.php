<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chọn ảnh</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans p-4">
<form method="GET" class="flex gap-2 mb-4 sticky top-0 bg-slate-50 py-1">
    <input type="hidden" name="picker" value="1">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên file…"
           class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600">
    <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg px-4 py-2">🔍</button>
</form>

@if($media->isEmpty())
    <div class="text-center py-16 text-slate-400 text-sm">
        <div class="text-5xl mb-3">🖼️</div>
        Chưa có ảnh nào. Hãy tải ảnh lên ở tab "Thư viện ảnh" trước.
    </div>
@else
<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
    @foreach($media as $m)
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white hover:shadow-md transition">
        <div class="aspect-square bg-slate-100 overflow-hidden">
            @if($m->isImage())
                <img src="{{ $m->url }}" alt="" class="w-full h-full object-cover" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center text-3xl">📄</div>
            @endif
        </div>
        <div class="p-2">
            <p class="text-[.7rem] truncate mb-1.5" title="{{ e($m->filename) }}">{{ e($m->filename) }}</p>
            <button onclick="window.parent.tkproPickMedia('{{ $m->url }}')"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg py-1.5">✔ Chọn</button>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $media->appends(['picker' => 1])->links() }}</div>
@endif
</body>
</html>

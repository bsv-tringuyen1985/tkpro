@extends('layouts.admin')

@section('title', 'Cài đặt website')

@section('breadcrumb')
<span class="text-slate-300">/</span><span class="font-semibold text-slate-700">Cài đặt website</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-1">⚙️ Cài đặt website</h1>
    <p class="text-sm text-slate-500 mb-6">Thông tin chung hiển thị trên toàn bộ website (tên, logo, hotline, mạng xã hội…).</p>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach($groups as $groupKey => $group)
        <div class="bg-white rounded-2xl border border-emerald-900/10 p-6 shadow-sm mb-6">
            <h2 class="font-bold mb-5">
                @if($groupKey === 'general') 🌐 @elseif($groupKey === 'contact') 📞 @else 🌍 @endif
                {{ $group['label'] }}
            </h2>
            <div class="space-y-5">
                @foreach($group['fields'] as $key => $field)
                <div>
                    <label class="block text-sm font-semibold mb-1.5">
                        {{ $field['label'] }}
                        @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                    </label>

                    @if($field['type'] === 'textarea')
                        <textarea name="{{ $key }}" rows="3"
                                  class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 @if($key === 'google_maps_embed') font-mono @endif">{{ old($key, $values[$key] ?? '') }}</textarea>
                        @if($key === 'google_maps_embed')
                        <p class="text-xs text-slate-400 mt-1">Dán toàn bộ mã iframe nhúng bản đồ từ Google Maps vào đây.</p>
                        @endif
                    @elseif($field['type'] === 'image')
                        <div class="flex flex-wrap items-center gap-3">
                            @if(!empty($values[$key]))
                            <img src="{{ $values[$key] }}" alt="" class="h-14 w-auto rounded-lg border bg-white px-1">
                            @endif
                            <input type="file" name="{{ $key }}_file" accept=".jpg,.jpeg,.png,.webp,.svg"
                                   class="text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                            @if(!empty($values[$key]))
                            <label class="text-xs text-slate-500 flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="{{ $key }}_remove" value="1" class="w-3.5 h-3.5"> Gỡ ảnh hiện tại
                            </label>
                            @endif
                        </div>
                    @else
                        <input type="{{ $field['type'] === 'email' ? 'email' : 'text' }}" name="{{ $key }}" value="{{ old($key, $values[$key] ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                    @endif

                    @error($key)<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex gap-3">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg px-8 py-3 text-sm">💾 Lưu cài đặt</button>
        </div>
    </form>
</div>
@endsection

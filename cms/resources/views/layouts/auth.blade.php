<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Đăng nhập quản trị') · {{ setting('site_name', 'TKPRO') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: { extend: { colors: { brand: { 600: '#087a3b', 700: '#065d2d', 900: '#063d20' }, danger: '#e32121' } } }
}
</script>
</head>
<body class="min-h-screen bg-gradient-to-br from-brand-900 via-brand-700 to-brand-600 flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-6">
            @if(setting('logo'))
                <img src="{{ setting('logo') }}" alt="logo" class="h-12 mx-auto mb-3">
            @else
                <div class="text-3xl font-extrabold text-brand-700 mb-1">TK<span class="text-brand-600">PRO</span></div>
            @endif
            <h1 class="text-xl font-bold text-slate-800">@yield('heading', 'Đăng nhập quản trị')</h1>
            <p class="text-sm text-slate-500 mt-1">@yield('subheading', 'Khu vực dành cho người quản lý website.')</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">✅ {{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm">⚠️ {{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
    <p class="text-center mt-5">
        <a href="/" class="text-emerald-100/90 hover:text-white text-sm">← Về trang chủ</a>
    </p>
</div>
</body>
</html>

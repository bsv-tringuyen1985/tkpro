<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Quản trị') · {{ setting('site_name', 'TKPRO') }}</title>
@if(setting('favicon'))
<link rel="icon" href="{{ setting('favicon') }}">
@endif
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eef7f1', 100: '#d9ecdf', 200: '#b3d9c2', 300: '#82bf9c',
                    400: '#4ea374', 500: '#1f8857', 600: '#087a3b', 700: '#065d2d',
                    800: '#054a24', 900: '#063d20', 950: '#032813'
                },
                danger: '#e32121',
                ink: '#10231a',
                paper: '#f6faf7'
            },
            fontFamily: {
                sans: ['"Be Vietnam Pro"', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'sans-serif']
            }
        }
    }
}
</script>
<style>
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #eef2ef; }
    ::-webkit-scrollbar-thumb { background: #b9c9be; border-radius: 8px; }
    .toast { animation: toast-in .25s ease-out; }
    @keyframes toast-in { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .toast.hide { transition: opacity .3s, transform .3s; opacity: 0; transform: translateY(8px); }
    .navlink.active { background: #065d2d; color: #fff; }
    input[type="checkbox"], input[type="radio"] { accent-color: #087a3b; }
    .prose-admin img { max-width: 100%; height: auto; border-radius: .5rem; }
    .table-wrap table { width: 100%; border-collapse: collapse; }
    .table-wrap th { text-align: left; font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #5b6f63; padding: .65rem .9rem; background: #f1f6f2; white-space: nowrap; }
    .table-wrap td { padding: .75rem .9rem; border-top: 1px solid #e6ede8; vertical-align: middle; font-size: .9rem; }
    .table-wrap tr:hover td { background: #f8fbf9; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 9999px; animation: spin .7s linear infinite; display: inline-block; vertical-align: -3px; }
</style>
@stack('head')
</head>
<body class="bg-paper text-ink font-sans antialiased">
<div class="min-h-screen flex">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="w-64 shrink-0 bg-brand-900 text-white flex flex-col fixed inset-y-0 z-30">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            @if(setting('logo'))
                <img src="{{ setting('logo') }}" alt="logo" class="h-9 w-auto bg-white rounded px-1">
            @else
                <span class="text-2xl font-extrabold tracking-tight">TK<span class="text-emerald-300">PRO</span></span>
            @endif
            <span class="text-xs uppercase tracking-widest text-emerald-200/80">Quản trị</span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-[.92rem]">
            <a href="{{ route('admin.dashboard') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>📊</span> Tổng quan
            </a>

            @if(has_permission('products.view'))
            <a href="{{ route('admin.products.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <span>📦</span> Sản phẩm
            </a>
            @endif
            @if(has_permission('product_categories.view'))
            <a href="{{ route('admin.product_categories.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.product_categories.*') ? 'active' : '' }}">
                <span>🗂️</span> Danh mục SP
            </a>
            @endif
            @if(has_permission('posts.view'))
            <a href="{{ route('admin.posts.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                <span>📰</span> Bài viết
            </a>
            @endif
            @if(has_permission('post_categories.view'))
            <a href="{{ route('admin.post_categories.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.post_categories.*') ? 'active' : '' }}">
                <span>🏷️</span> Chuyên mục
            </a>
            @endif
            @if(has_permission('pages.view'))
            <a href="{{ route('admin.pages.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <span>📄</span> Trang tĩnh
            </a>
            @endif
            @if(has_permission('banners.view'))
            <a href="{{ route('admin.banners.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <span>🖼️</span> Banner
            </a>
            @endif
            @if(has_permission('menus.view'))
            <a href="{{ route('admin.menus.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.menus.*') || request()->routeIs('admin.menu_items.*') ? 'active' : '' }}">
                <span>🧭</span> Menu
            </a>
            @endif
            @if(has_permission('media.view'))
            <a href="{{ route('admin.media.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                <span>🖼️</span> Thư viện ảnh
            </a>
            @endif
            @if(has_permission('contact_messages.view'))
            <a href="{{ route('admin.contact_messages.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.contact_messages.*') ? 'active' : '' }}">
                <span>✉️</span> Liên hệ
                @if(($unreadMessages ?? 0) > 0)
                    <span class="ml-auto bg-danger text-white text-xs font-bold rounded-full min-w-[1.4rem] h-[1.4rem] inline-flex items-center justify-center px-1">{{ $unreadMessages }}</span>
                @endif
            </a>
            @endif

            @if(has_permission('users.view') || has_permission('roles.view') || has_permission('settings.view'))
            <div class="pt-3 mt-3 border-t border-white/10 text-[.72rem] uppercase tracking-widest text-emerald-200/60 px-3">Hệ thống</div>
            @endif
            @if(has_permission('users.view'))
            <a href="{{ route('admin.users.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span>👥</span> Người dùng
            </a>
            @endif
            @if(has_permission('roles.view'))
            <a href="{{ route('admin.roles.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <span>🛡️</span> Vai trò & quyền
            </a>
            @endif
            @if(has_permission('settings.view'))
            <a href="{{ route('admin.settings.index') }}" class="navlink flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span>⚙️</span> Cài đặt
            </a>
            @endif
        </nav>

        <div class="px-4 py-4 border-t border-white/10 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center font-bold">{{ mb_substr(auth()->user()->name ?? '?', 0, 1) }}</span>
                <div class="min-w-0">
                    <div class="font-semibold truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-emerald-200/70 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('admin.password.change') }}" class="flex-1 text-center text-xs bg-white/10 hover:bg-white/20 rounded-lg px-2 py-2">🔑 Đổi MK</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="flex-1">
                    @csrf
                    <button class="w-full text-xs bg-danger/90 hover:bg-danger rounded-lg px-2 py-2">🚪 Đăng xuất</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-emerald-900/10">
            <div class="px-6 py-3.5 flex items-center gap-4">
                <nav class="text-sm text-slate-500 flex items-center gap-2 min-w-0">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-700">🏠</a>
                    @yield('breadcrumb')
                </nav>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ config('app.frontend_url', '/') }}" target="_blank" class="text-sm text-brand-700 hover:underline">Xem website →</a>
                </div>
            </div>
        </header>

        <main class="flex-1 px-6 py-6 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>

        <footer class="px-6 py-4 text-xs text-slate-400 border-t border-emerald-900/10">
            {{ setting('copyright', '© 2026 TKPRO') }} · Trang quản trị
        </footer>
    </div>
</div>

{{-- ============ TOASTS ============ --}}
<div id="toasts" class="fixed bottom-5 right-5 z-50 space-y-2 w-80">
    @foreach(['success' => '✅', 'error' => '❌', 'warning' => '⚠️'] as $type => $icon)
        @if(session($type))
        <div class="toast flex items-start gap-2 bg-white border-l-4 {{ $type === 'success' ? 'border-brand-600' : ($type === 'error' ? 'border-danger' : 'border-amber-500') }} shadow-lg rounded-lg px-4 py-3 text-sm">
            <span>{{ $icon }}</span>
            <span class="flex-1">{{ session($type) }}</span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700">✕</button>
        </div>
        @endif
    @endforeach
    @if($errors->any())
    <div class="toast bg-white border-l-4 border-danger shadow-lg rounded-lg px-4 py-3 text-sm">
        <div class="font-semibold text-danger mb-1">❌ Vui lòng kiểm tra lại:</div>
        <ul class="list-disc pl-5 space-y-0.5 text-slate-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

{{-- ============ MODAL CHỌN ẢNH TỪ THƯ VIỆN ============ --}}
<div id="mediaModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/60" onclick="closeMediaPicker()"></div>
    <div class="absolute inset-x-4 top-[4%] bottom-[4%] md:inset-x-16 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b">
            <h3 class="font-bold text-lg">🖼️ Chọn ảnh từ thư viện</h3>
            <button onclick="closeMediaPicker()" class="text-2xl text-slate-400 hover:text-slate-700 leading-none">✕</button>
        </div>
        <iframe id="mediaFrame" src="" class="flex-1 w-full border-0"></iframe>
    </div>
</div>

<script>
// --- Dialog xác nhận xóa / hành động nguy hiểm ---
document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.matches('form[data-confirm]')) {
        if (!confirm(form.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    }
});

// --- Loading state cho nút submit ---
document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.matches('form[data-confirm]') || !form.checkValidity()) return;
    var btn = form.querySelector('button[type="submit"]');
    if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.setAttribute('data-orig', btn.innerHTML);
        btn.innerHTML = '<span class="spinner"></span> Đang xử lý…';
        setTimeout(function () { btn.disabled = false; btn.innerHTML = btn.getAttribute('data-orig'); }, 8000);
    }
});

// --- Tự ẩn toast sau 6 giây ---
setTimeout(function () {
    document.querySelectorAll('#toasts .toast').forEach(function (t) {
        t.classList.add('hide');
        setTimeout(function () { t.remove(); }, 350);
    });
}, 6000);

// --- Media picker (modal chọn ảnh dùng lại) ---
var mediaPickerTarget = null;
function openMediaPicker(targetId) {
    mediaPickerTarget = targetId;
    document.getElementById('mediaFrame').src = '{{ route('admin.media.index') }}?picker=1';
    document.getElementById('mediaModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMediaPicker() {
    document.getElementById('mediaModal').classList.add('hidden');
    document.getElementById('mediaFrame').src = '';
    document.body.style.overflow = '';
    mediaPickerTarget = null;
}
function tkproPickMedia(url) {
    if (!mediaPickerTarget) return;
    var el = document.getElementById(mediaPickerTarget);
    if (!el) return;
    if (el.tagName === 'TEXTAREA') {
        // Chèn thẻ <img> vào vị trí con trỏ trong nội dung bài viết/trang.
        var img = '<img src="' + url + '" alt="">';
        var start = el.selectionStart || el.value.length;
        var end = el.selectionEnd || el.value.length;
        el.value = el.value.substring(0, start) + img + el.value.substring(end);
        el.focus();
    } else {
        el.value = url;
        var preview = document.getElementById(mediaPickerTarget + '_preview');
        if (preview) { preview.src = url; preview.classList.remove('hidden'); }
        var hint = document.getElementById(mediaPickerTarget + '_name');
        if (hint) { hint.textContent = url.split('/').pop(); }
    }
    closeMediaPicker();
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMediaPicker();
});
</script>
@stack('scripts')
</body>
</html>

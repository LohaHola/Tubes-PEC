<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B1F3A">
    <title>@yield('title', 'TubesPEC')</title>
    <link rel="stylesheet" href="{{ asset('css/tubespec.css') }}">
</head>
<body class="{{ auth()->check() && auth()->user()->role === 'admin' ? 'admin-body' : 'account-body' }}">
    @if (auth()->check() && auth()->user()->role === 'admin')
        <div class="admin-shell">
            <aside class="admin-sidebar" id="adminSidebar">
                <a href="{{ route('admin.dashboard') }}" class="admin-brand">TubesPEC<span>.</span></a>
                @include('layouts.partials.admin-navigation')
                <div class="admin-sidebar-footer">
                    <div class="admin-user-name">{{ auth()->user()->name }}</div>
                    <div class="admin-user-role">Administrator</div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="button admin-logout">Keluar dari Workspace</button>
                    </form>
                </div>
            </aside>

            <button type="button" class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-label="Tutup navigasi"></button>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div style="display:flex; align-items:center; gap:13px;">
                        <button type="button" class="admin-mobile-toggle" id="adminMenuToggle" aria-label="Buka navigasi" aria-controls="adminSidebar" aria-expanded="false">☰</button>
                        <div>
                            <div class="admin-topbar-title">@yield('page-heading', trim($__env->yieldContent('title', 'Workspace Administrator')))</div>
                            <div class="admin-topbar-context">TubesPEC / Administrator</div>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="admin-topbar-user">Lihat Toko ↗</a>
                </header>

                <main class="container">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-error">{{ session('error') }}</div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
        <script>
            (function () {
                const sidebar = document.getElementById('adminSidebar');
                const toggle = document.getElementById('adminMenuToggle');
                const backdrop = document.getElementById('adminSidebarBackdrop');

                if (!sidebar || !toggle || !backdrop) return;

                function setMenu(open) {
                    sidebar.classList.toggle('is-open', open);
                    backdrop.classList.toggle('is-visible', open);
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    document.body.style.overflow = open ? 'hidden' : '';
                }

                toggle.addEventListener('click', function () {
                    setMenu(!sidebar.classList.contains('is-open'));
                });
                backdrop.addEventListener('click', function () {
                    setMenu(false);
                });
                sidebar.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () { setMenu(false); });
                });
                window.addEventListener('resize', function () {
                    if (window.innerWidth > 900) setMenu(false);
                });
            })();
        </script>
    @else
        @include('layouts.partials.customer-navigation')
        <main class="container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
        <footer class="store-footer">© {{ date('Y') }} <strong>TubesPEC</strong>. Teknologi untuk kebutuhan digital.</footer>
    @endif
</body>
</html>
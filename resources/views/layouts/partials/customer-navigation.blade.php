@php
    $isCustomer = auth()->check() && auth()->user()->role === 'customer';
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
@endphp

<header class="store-header">
    <div class="header-inner">
        <a href="{{ route('home') }}" class="store-logo" aria-label="Arbyte beranda">Arbyte<span>.</span></a>

        <form action="{{ route('storefront.products.index') }}" method="GET" class="header-search" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari komputer, periferal, audio..." aria-label="Cari produk">
            <button type="submit" aria-label="Cari">⌕</button>
        </form>

        <nav class="store-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>
            <a href="{{ route('storefront.products.index') }}" class="{{ request()->routeIs('storefront.products.*') ? 'is-active' : '' }}">Produk</a>
            @if ($isCustomer)
                <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'is-active' : '' }}">Keranjang</a>
                <a href="{{ route('customer.orders.index') }}" class="{{ request()->routeIs('customer.orders.*') ? 'is-active' : '' }}">Pesanan</a>
            @endif
        </nav>

        <div class="header-user">
            @auth
                @if ($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="header-user-name">Admin Workspace</a>
                @else
                    <details class="account-menu">
                        <summary class="account-menu-trigger" aria-label="Buka menu akun" title="Menu akun">
                            @if (auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="" class="account-menu-photo">
                            @else
                                <span class="account-menu-fallback" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M4.5 20c.8-3.5 3.3-5.5 7.5-5.5s6.7 2 7.5 5.5"></path></svg></span>
                            @endif
                            <span class="account-menu-chevron" aria-hidden="true">⌄</span>
                        </summary>
                        <div class="account-menu-popover">
                            <div class="account-menu-identity">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </div>
                            <a href="{{ route('customer.profile.edit') }}" class="{{ request()->routeIs('customer.profile.*') ? 'is-current' : '' }}">
                                <span aria-hidden="true">◉</span> Profil Saya
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><span aria-hidden="true">↪</span> Keluar</button>
                            </form>
                        </div>
                    </details>
                @endif
            @else
                <a href="{{ route('login') }}" class="header-user-name">Masuk</a>
                <a href="{{ route('register') }}" class="button">Daftar</a>
            @endauth
        </div>
    </div>
</header>

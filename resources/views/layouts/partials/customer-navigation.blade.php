@php
    $isCustomer = auth()->check() && auth()->user()->role === 'customer';
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
@endphp

<header class="store-header">
    <div class="header-inner">
        <a href="{{ route('home') }}" class="store-logo" aria-label="TubesPEC beranda">TubesPEC<span>.</span></a>

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
                    <a href="{{ route('dashboard') }}" class="header-user-name">{{ auth()->user()->name }}</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="button button-light button-logout">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="header-user-name">Masuk</a>
                <a href="{{ route('register') }}" class="button">Daftar</a>
            @endauth
        </div>
    </div>
</header>
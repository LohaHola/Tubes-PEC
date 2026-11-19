<nav class="admin-sidebar-nav" aria-label="Navigasi administrator">
    <div class="admin-nav-caption">WORKSPACE</div>

    <a href="{{ route('admin.dashboard') }}"
       class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
        <span class="admin-nav-icon" aria-hidden="true">▦</span>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('admin.products.index') }}"
       class="admin-nav-link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
        <span class="admin-nav-icon" aria-hidden="true">▤</span>
        <span>Produk</span>
    </a>

    <a href="{{ route('admin.categories.index') }}"
       class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">
        <span class="admin-nav-icon" aria-hidden="true">◇</span>
        <span>Kategori</span>
    </a>

    <a href="{{ route('admin.orders.index') }}"
       class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">
        <span class="admin-nav-icon" aria-hidden="true">▣</span>
        <span>Pesanan</span>
    </a>

    <a href="{{ route('admin.customers.index') }}"
       class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}">
        <span class="admin-nav-icon" aria-hidden="true">♙</span>
        <span>Customer</span>
    </a>

    <div class="admin-nav-divider"></div>

    <a href="{{ route('home') }}" class="admin-nav-link">
        <span class="admin-nav-icon" aria-hidden="true">↗</span>
        <span>Lihat Toko</span>
    </a>
</nav>
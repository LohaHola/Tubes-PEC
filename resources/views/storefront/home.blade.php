@extends('layouts.storefront')

@section('title', 'Arbyte — Technology for Everyday')

@section('content')
<section class="hero-ecosystem">
    <div class="hero-copy">
        <div class="hero-eyebrow">Technology ecosystem</div>
        <h1>Temukan perangkat yang tepat untuk duniamu.</h1>
        <p>
            Jelajahi komputer, periferal, audio, display, dan aksesoris digital
            dengan informasi produk yang lebih jelas untuk membantu pilihanmu.
        </p>
        <div class="hero-actions">
            <a href="{{ route('storefront.products.index') }}" class="store-button">Jelajahi Produk <span aria-hidden="true">→</span></a>
            <a href="#kategori" class="store-button hero-secondary">Lihat Kategori</a>
        </div>
        <div class="hero-proof">
            <span>Informasi spesifikasi</span>
            <span>Stok transparan</span>
            <span>Pembayaran aman</span>
        </div>
    </div>
</section>

<section class="home-section" id="kategori">
    <div class="section-heading">
        <div>
            <h2>Jelajahi kategori</h2>
            <div class="section-subtitle">Temukan perangkat berdasarkan kebutuhan teknologi kamu.</div>
        </div>
    </div>

    @if ($categories->isNotEmpty())
        <div class="category-list">
            @foreach ($categories as $category)
                <a href="{{ route('storefront.products.index', ['category' => $category->id]) }}" class="category-link">
                    <span>{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    @else
        <div class="empty-state">Kategori produk akan ditampilkan di sini setelah tersedia.</div>
    @endif
</section>

<section class="home-section">
    <div class="section-heading">
        <div>
            <h2>Produk terbaru</h2>
            <div class="section-subtitle">Pilihan perangkat teknologi yang tersedia di katalog Arbyte.</div>
        </div>
        <a href="{{ route('storefront.products.index') }}" class="admin-link">Lihat semua produk →</a>
    </div>

    @if ($products->isNotEmpty())
        <div class="product-grid">
            @foreach ($products as $product)
                @include('storefront.products._card', ['product' => $product])
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <h3>Belum ada produk</h3>
            <p>Produk yang ditambahkan oleh admin akan muncul di katalog ini.</p>
        </div>
    @endif
</section>

<section class="home-section">
    <div class="section-heading">
        <div>
            <h2>Belanja dengan informasi yang jelas</h2>
            <div class="section-subtitle">Hal penting yang membantu kamu memilih perangkat dengan lebih percaya diri.</div>
        </div>
    </div>
    <div class="trust-grid">
        <article class="dashboard-card">
            <div class="product-category">01 / PRODUCT INFO</div>
            <h3>Spesifikasi terstruktur</h3>
            <p class="section-subtitle">Informasi produk disajikan agar lebih mudah diperiksa dan dibandingkan.</p>
        </article>
        <article class="dashboard-card">
            <div class="product-category">02 / AVAILABILITY</div>
            <h3>Ketersediaan transparan</h3>
            <p class="section-subtitle">Informasi stok ditampilkan sebelum kamu membuat pesanan.</p>
        </article>
        <article class="dashboard-card">
            <div class="product-category">03 / CHECKOUT</div>
            <h3>Pembayaran terintegrasi</h3>
            <p class="section-subtitle">Pembayaran diproses melalui halaman checkout Midtrans yang terintegrasi.</p>
        </article>
    </div>
</section>
@endsection
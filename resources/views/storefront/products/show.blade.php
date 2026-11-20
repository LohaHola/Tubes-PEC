@extends('layouts.storefront')

@section('title', $product->name . ' - Arbyte')

@section('content')
@include('layouts.partials.back-link', ['href' => route('storefront.products.index'), 'label' => 'Kembali ke Katalog'])

<div class="breadcrumbs">
    <a href="{{ route('home') }}">Beranda</a>
    <span>/</span>
    <a href="{{ route('storefront.products.index') }}">Produk</a>
    <span>/</span>
    <span>{{ $product->name }}</span>
</div>

<div class="detail-grid">
    <div>
        @if ($product->image_path)
            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="detail-image">
        @else
            <div class="image-placeholder" role="img" aria-label="Gambar produk belum tersedia" style="border-radius:12px;">Gambar belum tersedia</div>
        @endif
    </div>

    <div>
        <div class="product-category">{{ $product->category?->name ?? 'Technology' }}</div>
        <h1 style="font-size:30px; line-height:1.3; margin:12px 0;">{{ $product->name }}</h1>

        <a href="#reviews" class="product-rating-link">
            <span class="review-stars">{{ $reviewCount ? str_repeat('★', (int) round($reviewAverage)) . str_repeat('☆', 5 - (int) round($reviewAverage)) : '☆☆☆☆☆' }}</span>
            <strong>{{ $reviewCount ? number_format($reviewAverage, 1) : 'Belum ada rating' }}</strong>
            <span>({{ $reviewCount }} ulasan)</span>
        </a>

        <div class="detail-price">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>

        <div class="detail-meta">
            <div><strong>SKU:</strong> {{ $product->sku ?? 'Tidak tersedia' }}</div>
            <div><strong>Kategori:</strong> {{ $product->category?->name ?? 'Technology' }}</div>
            <div><strong>Ketersediaan:</strong>
                @if ($product->stock > 0)
                    <span class="stock-available">Tersedia · {{ $product->stock }} unit</span>
                @else
                    <span class="stock-unavailable">Stok habis</span>
                @endif
            </div>
        </div>

        @if (auth()->check() && auth()->user()->role === 'customer')
            @if ($product->stock > 0)
                <form action="{{ route('cart.store') }}" method="POST" class="product-buy-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <label for="quantity">Jumlah</label>
                    <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" required>
                    <button type="submit" class="store-button">Tambah ke Keranjang</button>
                </form>
            @else
                <div class="review-notice">Produk sedang habis. Silakan cek kembali nanti.</div>
            @endif
        @elseif (!auth()->check())
            <a href="{{ route('login') }}" class="store-button">Masuk untuk Membeli</a>
            <p class="profile-section-help" style="margin-top:10px;">Belum punya akun? <a href="{{ route('register') }}" class="admin-link">Daftar di sini</a>.</p>
        @endif

        <h2 style="font-size:18px; margin-top:28px;">Deskripsi Produk</h2>
        <div class="detail-description">{{ $product->description ?: 'Belum ada deskripsi untuk produk ini.' }}</div>
    </div>
</div>

@if ($product->specifications->isNotEmpty())
    <section class="profile-section" style="margin-top:28px;">
        <h2>Spesifikasi Produk</h2>
        <table class="spec-table">
            @foreach ($product->specifications as $specification)
                <tr><td>{{ $specification->name }}</td><td>{{ $specification->value }}</td></tr>
            @endforeach
        </table>
    </section>
@endif

@include('storefront.products._reviews')

@if ($relatedProducts->isNotEmpty())
    <section style="margin-top:40px;">
        <div class="section-heading"><h2>Produk Serupa</h2></div>
        <div class="product-grid">
            @foreach ($relatedProducts as $relatedProduct)
                @include('storefront.products._card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </section>
@endif

@endsection

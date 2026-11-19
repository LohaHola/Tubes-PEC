@extends('layouts.storefront')

@section('title', 'TubesPEC - Belanja Online')

@section('content')

<section class="hero">
    <h1>Temukan Produk yang Kamu Butuhkan</h1>

    <p>
        Selamat datang di TubesPEC.
        Jelajahi berbagai produk pilihan dengan mudah
        melalui katalog toko kami.
    </p>

    <a href="{{ route('storefront.products.index') }}"
       class="store-button">
        Jelajahi Produk
    </a>
</section>

<section>
    <div class="section-heading">
        <div>
            <h2>Kategori Produk</h2>
            <div class="section-subtitle">
                Temukan produk berdasarkan kategorinya.
            </div>
        </div>
    </div>

    @if ($categories->isNotEmpty())
        <div class="category-list">
            @foreach ($categories as $category)
                <a
                    href="{{ route('storefront.products.index', ['category' => $category->id]) }}"
                    class="category-link"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            Belum ada kategori produk.
        </div>
    @endif
</section>

<section>
    <div class="section-heading">
        <div>
            <h2>Produk Terbaru</h2>
            <div class="section-subtitle">
                Produk terbaru yang tersedia di toko kami.
            </div>
        </div>

        <a href="{{ route('storefront.products.index') }}">
            Lihat Semua &rarr;
        </a>
    </div>

    @if ($products->isNotEmpty())
        <div class="product-grid">
            @foreach ($products as $product)
                @include('storefront.products._card', [
                    'product' => $product
                ])
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <h3>Belum Ada Produk</h3>
            <p>
                Produk yang ditambahkan oleh admin akan muncul di sini.
            </p>
        </div>
    @endif
</section>

@endsection
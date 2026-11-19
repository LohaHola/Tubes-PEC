@extends('layouts.storefront')

@section('title', 'Katalog Produk - TubesPEC')

@section('content')

@include('layouts.partials.back-link', ['href' => route('home'), 'label' => 'Kembali ke Beranda'])

<div class="section-heading" style="margin-top:0;">
    <div>
        <h1 style="margin:0;">Katalog Produk</h1>

        <div class="section-subtitle">
            Temukan produk yang sesuai dengan kebutuhanmu.
        </div>
    </div>
</div>

<form
    action="{{ route('storefront.products.index') }}"
    method="GET"
    class="search-form"
>
    <input
        type="text"
        name="q"
        class="store-input"
        value="{{ request('q') }}"
        placeholder="Cari nama, SKU, atau deskripsi produk..."
    >

    <select name="category" class="store-select">
        <option value="">Semua Kategori</option>

        @foreach ($categories as $category)
            <option
                value="{{ $category->id }}"
                @selected(request('category') == $category->id)
            >
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="store-button">
        Cari Produk
    </button>

    <a
        href="{{ route('storefront.products.index') }}"
        class="store-button button-light"
    >
        Reset
    </a>
</form>

<div class="section-heading">
    <div>
        <h2>Daftar Produk</h2>

        <div class="section-subtitle">
            Menampilkan {{ $products->total() }} produk.
        </div>
    </div>
</div>

@if ($products->isNotEmpty())

    <div class="product-grid">
        @foreach ($products as $product)
            @include('storefront.products._card', [
                'product' => $product
            ])
        @endforeach
    </div>

    <nav class="catalog-pagination" aria-label="Navigasi halaman katalog">
        <div class="catalog-pagination-summary">
            <span class="catalog-pagination-label">Halaman</span>
            <strong>{{ $products->currentPage() }}</strong>
            <span>dari</span>
            <strong>{{ $products->lastPage() }}</strong>
        </div>

        <div class="catalog-pagination-controls">
            @if ($products->onFirstPage())
                <span class="catalog-page-button is-disabled" aria-disabled="true">
                    <span aria-hidden="true">←</span>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a class="catalog-page-button" href="{{ $products->previousPageUrl() }}" rel="prev">
                    <span aria-hidden="true">←</span>
                    <span>Sebelumnya</span>
                </a>
            @endif

            <div class="catalog-page-numbers" aria-label="Pilih halaman">
                @for ($page = 1; $page <= $products->lastPage(); $page++)
                    @if ($page === $products->currentPage())
                        <span class="catalog-page-number is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="catalog-page-number" href="{{ $products->url($page) }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endfor
            </div>

            @if ($products->hasMorePages())
                <a class="catalog-page-button catalog-page-next" href="{{ $products->nextPageUrl() }}" rel="next">
                    <span>Berikutnya</span>
                    <span aria-hidden="true">→</span>
                </a>
            @else
                <span class="catalog-page-button catalog-page-next is-disabled" aria-disabled="true">
                    <span>Berikutnya</span>
                    <span aria-hidden="true">→</span>
                </span>
            @endif
        </div>
    </nav>

@else

    <div class="empty-state">
        <h3>Produk Tidak Ditemukan</h3>

        <p>
            Coba gunakan kata kunci atau kategori yang berbeda.
        </p>

        <a
            href="{{ route('storefront.products.index') }}"
            class="store-button"
        >
            Lihat Semua Produk
        </a>
    </div>

@endif

@endsection

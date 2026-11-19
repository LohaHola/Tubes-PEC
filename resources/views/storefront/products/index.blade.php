@extends('layouts.storefront')

@section('title', 'Katalog Produk - TubesPEC')

@section('content')

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

    <div class="pagination-wrapper">
        {{ $products->links() }}
    </div>

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

@extends('layouts.storefront')

@section('title', $product->name . ' - TubesPEC')

@section('content')

<div style="margin-bottom:25px; font-size:14px; color:#7a8390;">
    <a href="{{ route('home') }}">Beranda</a>
    /
    <a href="{{ route('storefront.products.index') }}">Produk</a>
    /
    {{ $product->name }}
</div>

<div class="detail-grid">

    <div>
        @if ($product->image_path)
            <img
                src="{{ asset('storage/' . $product->image_path) }}"
                alt="{{ $product->name }}"
                class="detail-image"
            >
        @else
            <div class="image-placeholder" style="border-radius:9px;">
                Tidak ada gambar
            </div>
        @endif
    </div>

    <div>
        <div class="product-category">
            {{ $product->category->name }}
        </div>

        <h1 style="font-size:30px; line-height:1.3; margin:12px 0;">
            {{ $product->name }}
        </h1>

        <div class="detail-price">
            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
        </div>

        <div class="detail-meta">
            <div>
                <strong>SKU:</strong>
                {{ $product->sku ?? 'Tidak tersedia' }}
            </div>

            <div>
                <strong>Kategori:</strong>
                {{ $product->category->name }}
            </div>

            <div>
                <strong>Ketersediaan:</strong>

                @if ($product->stock > 0)
                    Stok tersedia ({{ $product->stock }})
                @else
                    Stok habis
                @endif
            
                @if (Auth::check() && Auth::user()->role === 'customer')

                    @if ($product->stock > 0)

                        <form action="{{ route('cart.store') }}"
                            method="POST"
                            style="margin:25px 0; display:flex; gap:10px; flex-wrap:wrap;">

                            @csrf

                            <input
                                type="hidden"
                                name="product_id"
                                value="{{ $product->id }}"
                            >

                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="{{ $product->stock }}"
                                required
                                style="width:100px; padding:12px; border:1px solid #dce1e7; border-radius:7px;"
                            >

                            <button type="submit" class="store-button">
                                + Tambah ke Keranjang
                            </button>

                        </form>

                    @else

                        <div style="color:#dc2626; margin:20px 0;">
                            Produk sedang habis.
                        </div>

                    @endif

                @elseif (!Auth::check())

                    <a href="{{ route('login') }}"
                    class="store-button"
                    style="margin-top:20px;">
                        Login untuk Membeli
                    </a>

                @endif

                @if ($errors->any())
                    <div class="alert" style="background:#fee2e2; color:#991b1b; margin-top:15px;">
                        {{ $errors->first() }}
                    </div>
                @endif

            </div>
        </div>

        <h3>Deskripsi Produk</h3>

        <div class="detail-description">
            {{ $product->description ?: 'Belum ada deskripsi untuk produk ini.' }}
        </div>

    </div>

</div>


@if ($product->specifications->isNotEmpty())
    <section style="margin-top:35px; background:white; border:1px solid #e8ebef; border-radius:12px; padding:25px;">

        <h2 style="margin-top:0;">Spesifikasi Produk</h2>

        <table class="spec-table">

            @foreach ($product->specifications as $specification)
                <tr>
                    <td>{{ $specification->name }}</td>
                    <td>{{ $specification->value }}</td>
                </tr>
            @endforeach

        </table>

    </section>
@endif

@if ($relatedProducts->isNotEmpty())
    <section style="margin-top:40px;">

        <div class="section-heading">
            <h2>Produk Serupa</h2>
        </div>

        <div class="product-grid">
            @foreach ($relatedProducts as $relatedProduct)
                @include('storefront.products._card', [
                    'product' => $relatedProduct
                ])
            @endforeach
        </div>

    </section>
@endif

<div style="margin-top:35px;">
    <a href="{{ route('storefront.products.index') }}"
       class="store-button button-light">
        &larr; Kembali ke Katalog
    </a>
</div>

@endsection

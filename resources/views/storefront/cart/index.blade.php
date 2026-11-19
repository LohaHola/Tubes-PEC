@extends('layouts.storefront')

@section('title', 'Keranjang Belanja - Arbyte')

@section('content')

@include('layouts.partials.back-link', ['href' => route('storefront.products.index'), 'label' => 'Kembali ke Katalog'])

<div style="margin-bottom:30px;">
    <h1 style="margin-bottom:8px;">Keranjang Belanja</h1>

    <p style="color:#77808e;">
        Periksa kembali produk yang ingin kamu beli.
    </p>
</div>

@if ($errors->any())
    <div class="alert" style="background:#fee2e2; color:#991b1b;">
        {{ $errors->first() }}
    </div>
@endif

@if ($cart->items->isEmpty())

    <div class="empty-state">
        <h2>Keranjang Masih Kosong</h2>

        <p>
            Kamu belum menambahkan produk ke keranjang.
        </p>

        <a href="{{ route('storefront.products.index') }}"
           class="store-button">
            Mulai Belanja
        </a>
    </div>

@else

    <div style="display:grid; grid-template-columns:minmax(0, 1fr) 310px; gap:22px; align-items:start;">

        <section style="display:flex; flex-direction:column; gap:15px;">

            @foreach ($cart->items as $item)

                @php
                    $product = $item->product;
                @endphp

                <div style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:18px; display:flex; gap:18px;">

                    <div style="width:105px; flex-shrink:0;">

                        @if ($product && $product->image_path)

                            <img
                                src="{{ asset('storage/' . $product->image_path) }}"
                                alt="{{ $product->name }}"
                                style="width:105px; height:105px; object-fit:cover; border-radius:8px;"
                            >

                        @else

                            <div class="image-placeholder"
                                 style="width:105px; height:105px; border-radius:8px;">
                                Tidak tersedia
                            </div>

                        @endif

                    </div>

                    <div style="flex:1; min-width:0;">

                        @if ($product)

                            <div class="product-category">
                                {{ $product->category->name ?? 'Kategori tidak tersedia' }}
                            </div>

                            <h3 style="margin:7px 0;">
                                {{ $product->name }}
                            </h3>

                            <strong style="font-size:16px;">
                                Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                            </strong>

                            @if (!$product->is_active)
                                <p style="color:#dc2626; font-size:13px;">
                                    Produk sudah tidak tersedia.
                                </p>

                            @elseif ($product->stock < $item->quantity)
                                <p style="color:#dc2626; font-size:13px;">
                                    Jumlah melebihi stok tersedia. Stok saat ini: {{ $product->stock }}
                                </p>

                            @else
                                <p class="product-stock">
                                    Stok tersedia: {{ $product->stock }}
                                </p>
                            @endif

                        @else

                            <h3 style="margin-top:5px;">Produk tidak tersedia</h3>

                            <p style="color:#dc2626; font-size:13px;">
                                Produk mungkin telah dihapus.
                            </p>

                        @endif

                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:15px;">

                            @if ($product && $product->is_active && $product->stock > 0)

                                <form
                                    action="{{ route('cart.update', $item) }}"
                                    method="POST"
                                    style="display:flex; gap:7px; align-items:center;"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="number"
                                        name="quantity"
                                        value="{{ $item->quantity }}"
                                        min="1"
                                        max="{{ $product->stock }}"
                                        style="width:75px; padding:9px; border:1px solid #dce1e7; border-radius:6px;"
                                        required
                                    >

                                    <button type="submit"
                                            class="store-button button-light"
                                            style="padding:9px 12px;">
                                        Perbarui
                                    </button>

                                </form>

                            @endif

                            <form
                                action="{{ route('cart.destroy', $item) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus produk dari keranjang?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        style="border:0; background:none; color:#dc2626; cursor:pointer; padding:8px;">
                                    Hapus
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            @endforeach

        </section>

        {{-- Order Summary --}}
        <aside style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:22px;">

            <h2 style="font-size:19px; margin-top:0;">
                Ringkasan Keranjang
            </h2>

            <div style="display:flex; justify-content:space-between; margin:20px 0;">
                <span style="color:#77808e;">Jumlah jenis produk</span>
                <strong>{{ $cart->items->count() }}</strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin:20px 0;">
                <span style="color:#77808e;">Subtotal</span>
                <strong>
                    Rp {{ number_format((float) $subtotal, 0, ',', '.') }}
                </strong>
            </div>

            <div style="border-top:1px solid #e7eaf0; padding-top:17px; margin-top:17px;">

                <div style="display:flex; justify-content:space-between; gap:10px; font-size:17px;">
                    <strong>Total sementara</strong>

                    <strong>
                        Rp {{ number_format((float) $subtotal, 0, ',', '.') }}
                    </strong>
                </div>

                <p style="font-size:12px; color:#89919d; line-height:1.6; margin-top:16px;">
                    Biaya pengiriman dan total akhir akan dihitung pada tahap checkout.
                </p>

                <a href="{{ route('storefront.products.index') }}"
                   class="store-button button-light"
                   style="display:block; text-align:center; margin-top:18px;">
                    Lanjut Belanja
                </a>

                <a href="{{ route('checkout.index') }}"
                class="store-button"
                style="display:block; text-align:center; margin-top:12px;">
                    Lanjut ke Checkout
                </a>

            </div>

        </aside>

    </div>

@endif

<style>
    @media (max-width: 750px) {
        .store-container > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 450px) {
        .store-container img {
            width:75px !important;
            height:75px !important;
        }
    }
</style>

@endsection

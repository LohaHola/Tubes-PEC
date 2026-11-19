@extends('layouts.storefront')

@section('title', 'Checkout - TubesPEC')

@section('content')

@include('layouts.partials.back-link', ['href' => route('cart.index'), 'label' => 'Kembali ke Keranjang'])

<div style="margin-bottom:28px;">
    <h1 style="margin-bottom:8px;">Checkout</h1>
    <p style="color:#77808e;">
        Lengkapi informasi penerima dan alamat pengiriman.
    </p>
</div>

@if ($errors->any())
    <div class="alert" style="background:#fee2e2; color:#991b1b;">
        <ul style="margin:0; padding-left:20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('checkout.store') }}" method="POST">

    @csrf

    <div style="display:grid; grid-template-columns:minmax(0, 1fr) 350px; gap:22px; align-items:start;">

        <section style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:24px;">

            <h2 style="margin-top:0; font-size:20px;">
                Informasi Pengiriman
            </h2>

            <div class="form-group">
                <label for="recipient_name">Nama Penerima</label>
                <input
                    type="text"
                    id="recipient_name"
                    name="recipient_name"
                    value="{{ old('recipient_name', Auth::user()->name) }}"
                    required
                    maxlength="255"
                >
            </div>

            <div class="form-group">
                <label for="recipient_phone">Nomor Telepon</label>
                <input
                    type="tel"
                    id="recipient_phone"
                    name="recipient_phone"
                    value="{{ old('recipient_phone') }}"
                    placeholder="Contoh: 081234567890"
                    required
                    maxlength="30"
                >
            </div>

            <div class="form-group">
                <label for="recipient_address">Alamat Lengkap</label>
                <textarea
                    id="recipient_address"
                    name="recipient_address"
                    rows="4"
                    required
                    maxlength="2000"
                    style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
                    placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan..."
                >{{ old('recipient_address') }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

                <div class="form-group">
                    <label for="city">Kota/Kabupaten</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city') }}"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label for="province">Provinsi</label>
                    <input
                        type="text"
                        id="province"
                        name="province"
                        value="{{ old('province') }}"
                        required
                        maxlength="100"
                    >
                </div>

            </div>

            <div class="form-group">
                <label for="postal_code">Kode Pos</label>
                <input
                    type="text"
                    id="postal_code"
                    name="postal_code"
                    value="{{ old('postal_code') }}"
                    required
                    maxlength="20"
                >
            </div>

            <div class="form-group">
                <label for="notes">Catatan Pesanan (Opsional)</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    maxlength="1000"
                    style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
                    placeholder="Catatan tambahan untuk pesanan..."
                >{{ old('notes') }}</textarea>
            </div>

        </section>

        <aside style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:22px;">

            <h2 style="margin-top:0; font-size:19px;">
                Ringkasan Pesanan
            </h2>

            @foreach ($cart->items as $item)

                <div style="display:flex; justify-content:space-between; gap:12px; padding:13px 0; border-bottom:1px solid #edf0f3;">

                    <div style="min-width:0;">
                        <strong style="font-size:14px;">
                            {{ $item->product->name }}
                        </strong>

                        <div style="color:#77808e; font-size:12px; margin-top:5px;">
                            {{ $item->quantity }} ×
                            Rp {{ number_format((float) $item->product->price, 0, ',', '.') }}
                        </div>
                    </div>

                    <strong style="font-size:13px; white-space:nowrap;">
                        Rp {{ number_format((float) $item->product->price * $item->quantity, 0, ',', '.') }}
                    </strong>

                </div>

            @endforeach

            <div style="display:flex; justify-content:space-between; margin-top:20px; font-size:14px;">
                <span>Subtotal</span>
                <strong>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:13px; font-size:14px;">
                <span>Ongkos Kirim</span>
                <strong>Rp {{ number_format((float) $shippingCost, 0, ',', '.') }}</strong>
            </div>

            <div style="border-top:1px solid #e7eaf0; margin-top:18px; padding-top:18px; display:flex; justify-content:space-between; gap:10px;">
                <strong>Total</strong>
                <strong style="font-size:18px;">
                    Rp {{ number_format((float) $total, 0, ',', '.') }}
                </strong>
            </div>

            <button type="submit"
                    class="store-button"
                    style="width:100%; margin-top:22px;">
                Buat Pesanan
            </button>

            <a href="{{ route('cart.index') }}"
               style="display:block; text-align:center; margin-top:15px; color:#64748b; font-size:13px;">
                Kembali ke Keranjang
            </a>

            <p style="margin-top:20px; font-size:11px; color:#89919d; line-height:1.6;">
                Harga dan stok diperiksa ulang saat pesanan dibuat.
                Ongkos kirim masih menggunakan nilai sementara.
            </p>

        </aside>

    </div>

</form>

<style>
    @media (max-width: 750px) {
        form > div[style*="grid-template-columns"] {
            grid-template-columns:1fr !important;
        }
    }
</style>

@endsection

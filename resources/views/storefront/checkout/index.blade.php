@extends('layouts.storefront')

@section('title', 'Checkout - Arbyte')

@section('content')
@include('layouts.partials.back-link', ['href' => route('cart.index'), 'label' => 'Kembali ke Keranjang'])

<div style="margin-bottom:28px;">
    <h1 style="margin-bottom:8px;">Checkout</h1>
    <p style="color:#77808e;">Periksa alamat penerima dan ringkasan belanja sebelum membuat pesanan.</p>
</div>

@if ($errors->any())
    <div class="alert alert-error">
        <strong>Pesanan belum dapat dibuat. Periksa data berikut:</strong>
        <ul style="margin:8px 0 0; padding-left:20px;">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form action="{{ route('checkout.store') }}" method="POST">
    @csrf
    <div class="checkout-layout">
        <section class="checkout-card">
            <div class="checkout-section-heading">
                <div>
                    <h2>Informasi Pengiriman</h2>
                    <p>Pastikan nomor telepon dan alamat dapat digunakan kurir.</p>
                </div>
                <a href="{{ route('customer.profile.edit') }}">Kelola profil</a>
            </div>

            <div class="form-group">
                <label for="recipient_name">Nama Penerima</label>
                <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', Auth::user()->name) }}" autocomplete="name" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="recipient_phone">Nomor Telepon</label>
                <input type="tel" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone', Auth::user()->phone) }}" autocomplete="tel" placeholder="Contoh: 081234567890" required maxlength="30">
            </div>
            <div class="form-group">
                <label for="recipient_address">Alamat Lengkap</label>
                <textarea id="recipient_address" name="recipient_address" rows="4" autocomplete="street-address" required maxlength="2000" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan...">{{ old('recipient_address', Auth::user()->address) }}</textarea>
            </div>
            <div class="checkout-fields-two">
                <div class="form-group">
                    <label for="city">Kota/Kabupaten</label>
                    <input type="text" id="city" name="city" value="{{ old('city', Auth::user()->city) }}" autocomplete="address-level2" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="province">Provinsi</label>
                    <input type="text" id="province" name="province" value="{{ old('province', Auth::user()->province) }}" autocomplete="address-level1" required maxlength="100">
                </div>
            </div>
            <div class="form-group">
                <label for="postal_code">Kode Pos</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', Auth::user()->postal_code) }}" autocomplete="postal-code" required maxlength="20">
            </div>
            <div class="form-group">
                <label for="notes">Catatan Pesanan <span class="profile-optional">(Opsional)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Contoh: titipkan di satpam jika tidak ada di rumah.">{{ old('notes') }}</textarea>
            </div>
        </section>

        <aside class="checkout-card checkout-summary">
            <h2>Ringkasan Pesanan</h2>
            @foreach ($cart->items as $item)
                <div class="checkout-item">
                    <div>
                        <strong>{{ $item->product->name }}</strong>
                        <small>{{ $item->quantity }} × Rp {{ number_format((float) $item->product->price, 0, ',', '.') }}</small>
                    </div>
                    <strong>Rp {{ number_format((float) $item->product->price * $item->quantity, 0, ',', '.') }}</strong>
                </div>
            @endforeach
            <div class="checkout-total-line"><span>Subtotal</span><strong>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</strong></div>
            <div class="checkout-total-line"><span>Ongkos Kirim</span><strong>Rp {{ number_format((float) $shippingCost, 0, ',', '.') }}</strong></div>
            <div class="checkout-grand-total"><span>Total Pembayaran</span><strong>Rp {{ number_format((float) $total, 0, ',', '.') }}</strong></div>
            <button type="submit" class="store-button checkout-submit">Buat Pesanan</button>
            <p class="checkout-hint">Stok dan harga diperiksa ulang saat pesanan dibuat. Ongkos kirim masih menggunakan nilai sementara.</p>
        </aside>
    </div>
</form>
@endsection

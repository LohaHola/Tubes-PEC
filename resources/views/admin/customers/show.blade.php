@extends('layouts.app')

@section('title', 'Detail Customer — Arbyte')

@section('content')
<div class="admin-page-v2">
    <a href="{{ route('admin.customers.index') }}" class="admin-back-link">← Kembali ke daftar customer</a>

    <div class="admin-customer-profile-head">
        <div class="admin-customer-avatar admin-customer-avatar-large">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto {{ $user->name }}">
            @else
                {{ strtoupper(substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div><span class="admin-eyebrow">CUSTOMER PROFILE / #{{ $user->id }}</span><h1>{{ $user->name }}</h1><p>{{ $user->email }} · Bergabung {{ $user->created_at->format('d M Y') }}</p></div>
    </div>

    <div class="admin-customer-detail-stats">
        <div><span>Total transaksi</span><strong>{{ number_format($user->orders()->count()) }}</strong></div>
        <div><span>Total dibayar</span><strong>Rp {{ number_format((float) $totalSpent, 0, ',', '.') }}</strong></div>
        <div><span>Nomor telepon</span><strong>{{ $user->phone ?: 'Belum diisi' }}</strong></div>
    </div>

    <section class="admin-analytics-panel">
        <div class="admin-panel-head"><div><span class="admin-eyebrow">ACCOUNT INFORMATION</span><h2>Informasi customer</h2><p>Perubahan data di sini akan memperbarui profil akun customer.</p></div></div>
        @if ($errors->any())
            <div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form action="{{ route('admin.customers.update', $user) }}" method="POST" class="admin-customer-edit-form">
            @csrf
            @method('PATCH')
            <div><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"></div>
            <div><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"></div>
            <div><label for="phone">Nomor telepon</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30"></div>
            <div><label for="city">Kota/Kabupaten</label><input id="city" name="city" value="{{ old('city', $user->city) }}" maxlength="100"></div>
            <div><label for="province">Provinsi</label><input id="province" name="province" value="{{ old('province', $user->province) }}" maxlength="100"></div>
            <div><label for="postal_code">Kode pos</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" maxlength="20"></div>
            <div class="admin-form-full"><label for="address">Alamat lengkap</label><textarea id="address" name="address" rows="3" maxlength="2000">{{ old('address', $user->address) }}</textarea></div>
            <div class="admin-form-full admin-form-actions"><button type="submit" class="admin-primary-link">Simpan perubahan</button></div>
        </form>
    </section>

    <section class="admin-analytics-panel">
        <div class="admin-panel-head"><div><span class="admin-eyebrow">ORDER HISTORY</span><h2>Riwayat pesanan</h2><p>Riwayat transaksi customer ini tetap tersimpan.</p></div></div>
        <div class="admin-table-scroll">
            <table class="admin-modern-table">
                <thead><tr><th>Nomor pesanan</th><th>Tanggal</th><th>Total</th><th>Pembayaran</th><th>Pengiriman</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                            <td>{{ match($order->payment_status) { 'paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', default => ucfirst($order->payment_status) } }}</td>
                            <td>{{ match($order->fulfillment_status) { 'unprocessed' => 'Belum diproses', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', default => ucfirst($order->fulfillment_status) } }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="admin-row-link">Detail →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty-state">Customer ini belum memiliki pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $orders->links() }}</div>
    </section>
</div>
@endsection

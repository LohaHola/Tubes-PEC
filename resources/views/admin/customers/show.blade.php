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
            <div>
                <label for="province_id">Provinsi</label>
                <select
                    id="province_id"
                    name="province_id"
                    class="region-select"
                    data-provinces-url="{{ route('regions.provinces') }}"
                    data-cities-url="{{ route('regions.cities', ['provinceId' => '__PROVINCE__']) }}"
                    data-selected-id="{{ old('province_id') }}"
                    data-current-name="{{ $user->province }}"
                >
                    <option value="">Memuat daftar provinsi...</option>
                </select>
                <small class="region-load-error" id="province-error" aria-live="polite"></small>
            </div>
            <div>
                <label for="city">Kota/Kabupaten</label>
                <select id="city" name="city" class="region-select" data-current-name="{{ old('city', $user->city) }}" disabled>
                    <option value="">Pilih provinsi terlebih dahulu</option>
                </select>
                <small class="region-load-error" id="city-error" aria-live="polite"></small>
            </div>
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

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city');
    const provinceError = document.getElementById('province-error');
    const cityError = document.getElementById('city-error');
    if (!provinceSelect || !citySelect) return;

    const setOption = (select, value, label) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        select.appendChild(option);
    };

    const loadCities = async (provinceId, selectedCity = '') => {
        citySelect.innerHTML = '';
        setOption(citySelect, '', provinceId ? 'Memuat kota/kabupaten...' : 'Pilih provinsi terlebih dahulu');
        citySelect.disabled = true;
        cityError.textContent = '';
        if (!provinceId) return;

        try {
            const url = provinceSelect.dataset.citiesUrl.replace('__PROVINCE__', encodeURIComponent(provinceId));
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Data kota/kabupaten tidak tersedia.');
            const result = await response.json();
            citySelect.innerHTML = '';
            setOption(citySelect, '', 'Pilih kota/kabupaten');
            result.data.forEach((city) => setOption(citySelect, city.name, city.name));
            citySelect.disabled = false;
            if (selectedCity) citySelect.value = selectedCity;
        } catch (error) {
            citySelect.innerHTML = '';
            setOption(citySelect, '', 'Gagal memuat daftar kota');
            cityError.textContent = 'Daftar kota gagal dimuat. Muat ulang halaman atau coba lagi.';
        }
    };

    try {
        const response = await fetch(provinceSelect.dataset.provincesUrl, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Data provinsi tidak tersedia.');
        const result = await response.json();
        provinceSelect.innerHTML = '';
        setOption(provinceSelect, '', 'Pilih provinsi');
        result.data.forEach((province) => setOption(provinceSelect, province.id, province.name));

        const selected = result.data.find((province) =>
            provinceSelect.dataset.selectedId
                ? province.id === provinceSelect.dataset.selectedId
                : province.name.toLowerCase() === (provinceSelect.dataset.currentName || '').toLowerCase()
        );
        if (selected) {
            provinceSelect.value = selected.id;
            await loadCities(selected.id, citySelect.dataset.currentName || '');
        }
    } catch (error) {
        provinceSelect.innerHTML = '';
        setOption(provinceSelect, '', 'Gagal memuat daftar provinsi');
        provinceError.textContent = 'Daftar provinsi gagal dimuat. Muat ulang halaman atau coba lagi.';
    }

    provinceSelect.addEventListener('change', () => loadCities(provinceSelect.value));
});
</script>

@endsection

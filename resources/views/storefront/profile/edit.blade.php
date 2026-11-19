@extends('layouts.storefront')

@section('title', 'Profil Saya - Arbyte')

@section('content')
@include('layouts.partials.back-link', ['href' => route('customer.dashboard'), 'label' => 'Kembali ke Dashboard'])

<div class="profile-page">
    <div class="profile-heading">
        <div>
            <div class="product-category">AKUN CUSTOMER</div>
            <h1>Profil Saya</h1>
            <p>Kelola foto, informasi akun, dan alamat pengirimanmu.</p>
        </div>
        <div class="profile-avatar profile-avatar-large">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto profil {{ $user->name }}">
            @else
                <span class="profile-avatar-fallback" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M4.5 20c.8-3.5 3.3-5.5 7.5-5.5s6.7 2 7.5 5.5"></path></svg></span>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Periksa kembali data yang kamu masukkan.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
        @csrf
        @method('PATCH')

        <section class="profile-section">
            <h2>Foto Profil</h2>
            <p class="profile-section-help">Gunakan foto JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.</p>
            <div class="profile-photo-editor">
                <div class="profile-avatar profile-avatar-preview">
                    @if ($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto profil saat ini">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="profile-photo-controls">
                    <label for="profile_photo">Pilih foto baru</label>
                    <input id="profile_photo" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small>Foto akan tampil pada ikon akun di navigasi toko.</small>
                    @if ($user->profile_photo_path)
                        <label class="profile-remove-photo">
                            <input type="checkbox" name="remove_profile_photo" value="1">
                            Hapus foto profil saat ini
                        </label>
                    @endif
                </div>
            </div>
        </section>

        <section class="profile-section">
            <h2>Informasi Akun</h2>
            <p class="profile-section-help">Informasi ini digunakan untuk mengenali akun kamu.</p>
            <div class="profile-fields">
                <div class="form-group">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="email">Alamat email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required maxlength="255">
                    <small>Email digunakan untuk masuk ke akun.</small>
                </div>
                <div class="form-group">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="Contoh: 081234567890" maxlength="30">
                </div>
            </div>
        </section>

        <section class="profile-section">
            <h2>Alamat Pengiriman</h2>
            <p class="profile-section-help">Simpan alamat agar kamu tidak perlu mengisinya dari awal setiap checkout.</p>
            <div class="form-group">
                <label for="address">Alamat lengkap</label>
                <textarea id="address" name="address" rows="3" autocomplete="street-address" maxlength="2000" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan...">{{ old('address', $user->address) }}</textarea>
            </div>
            <div class="profile-fields">
                <div class="form-group">
                    <label for="province_id">Provinsi</label>
                    <select
                        id="province_id"
                        name="province_id"
                        class="region-select"
                        data-provinces-url="{{ route('regions.provinces') }}"
                        data-cities-url="{{ route('regions.cities', ['provinceId' => '__PROVINCE__']) }}"
                        data-selected-id="{{ old('province_id') }}"
                        data-current-name="{{ $user->province }}"
                        autocomplete="address-level1"
                    >
                        <option value="">Memuat daftar provinsi...</option>
                    </select>
                    <small class="region-load-error" id="province-error" aria-live="polite"></small>
                </div>
                <div class="form-group">
                    <label for="city">Kota/Kabupaten</label>
                    <select id="city" name="city" class="region-select" data-current-name="{{ old('city', $user->city) }}" autocomplete="address-level2" disabled>
                        <option value="">Pilih provinsi terlebih dahulu</option>
                    </select>
                    <small class="region-load-error" id="city-error" aria-live="polite"></small>
                </div>
                <div class="form-group">
                    <label for="postal_code">Kode pos</label>
                    <input id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" autocomplete="postal-code" maxlength="20">
                </div>
            </div>
        </section>

        <div class="profile-actions">
            <a href="{{ route('customer.dashboard') }}" class="store-button button-light">Batal</a>
            <button type="submit" class="store-button">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city');
    const provinceError = document.getElementById('province-error');
    const cityError = document.getElementById('city-error');
    if (!provinceSelect || !citySelect) return;

    const citiesUrl = provinceSelect.dataset.citiesUrl;
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
            const url = citiesUrl.replace('__PROVINCE__', encodeURIComponent(provinceId));
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

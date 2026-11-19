@extends('layouts.app')

@section('title', ($feature->exists ? 'Edit' : 'Tambah') . ' Promo & Rekomendasi - Arbyte')

@section('content')
@include('layouts.partials.back-link', ['href' => route('admin.homepage-features.index'), 'label' => 'Kembali'])

<div class="dashboard-card" style="max-width:900px;">
    <h1>{{ $feature->exists ? 'Edit Konten Homepage' : 'Tambah Konten Homepage' }}</h1>
    <p class="subtitle">Pilih jenis konten, produk, dan pengaturan tampilnya di halaman utama.</p>

    @if ($errors->any())
        <div class="alert alert-error"><ul style="margin:0;padding-left:20px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $feature->exists ? route('admin.homepage-features.update', $feature) : route('admin.homepage-features.store') }}">
        @csrf
        @if ($feature->exists) @method('PUT') @endif

        <div class="form-group">
            <label for="type">Jenis Konten</label>
            <select id="type" name="type" required>
                <option value="promo" @selected(old('type', $feature->type) === 'promo')>Promo</option>
                <option value="recommendation" @selected(old('type', $feature->type) === 'recommendation')>Rekomendasi</option>
            </select>
        </div>

        <div class="form-group">
            <label for="product_id">Produk</label>
            <select id="product_id" name="product_id" required>
                <option value="">Pilih produk</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id', $feature->product_id) === (string) $product->id)>
                        {{ $product->name }} — Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="section_title">Judul Section</label>
            <input id="section_title" name="section_title" value="{{ old('section_title', $feature->section_title) }}" maxlength="120" placeholder="Contoh: Promo Spesial Minggu Ini">
            <small>Judul yang sama akan digunakan untuk seluruh item dalam jenis section tersebut.</small>
        </div>

        <div class="form-group">
            <label for="item_title">Label Produk</label>
            <input id="item_title" name="item_title" value="{{ old('item_title', $feature->item_title) }}" maxlength="160" placeholder="Contoh: Diskon Terbatas / Pilihan Admin">
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description', $feature->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="promo_price">Harga Promo (khusus promo)</label>
            <input id="promo_price" type="number" name="promo_price" min="0" step="0.01" value="{{ old('promo_price', $feature->promo_price) }}" placeholder="Kosongkan jika tidak mengubah harga">
        </div>

        <div class="form-group">
            <label for="starts_at">Mulai Tayang (opsional)</label>
            <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $feature->starts_at?->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="form-group">
            <label for="ends_at">Akhir Tayang (opsional)</label>
            <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $feature->ends_at?->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="form-group">
            <label for="sort_order">Urutan Tampil</label>
            <input id="sort_order" type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $feature->sort_order ?? 0) }}">
        </div>

        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
            <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $feature->is_active)) style="width:auto;">
            <label for="is_active" style="margin:0;">Aktifkan dan tampilkan di homepage</label>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;">
            <button type="submit" class="button">Simpan</button>
            <a href="{{ route('admin.homepage-features.index') }}" class="button" style="background:#64748b;">Batal</a>
        </div>
    </form>
</div>
@endsection

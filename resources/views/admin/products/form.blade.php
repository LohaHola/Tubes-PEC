@extends('layouts.app')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="auth-card" style="max-width:650px;">

    <h1>{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h1>

    <p class="subtitle">Lengkapi informasi produk toko.</p>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="alert alert-error">
            Belum ada kategori. Buat kategori terlebih dahulu sebelum menambahkan produk.
        </div>
    @endif

    <form
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="category_id">Kategori</label>

            <select
                name="category_id"
                id="category_id"
                required
                style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
            >
                <option value="">Pilih Kategori</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(old('category_id', $product->category_id) == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="name">Nama Produk</label>

            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $product->name) }}"
                placeholder="Nama produk"
                required
            >
        </div>

        <div class="form-group">
            <label for="sku">SKU</label>

            <input
                type="text"
                name="sku"
                id="sku"
                value="{{ old('sku', $product->sku) }}"
                placeholder="Kode SKU (opsional)"
            >
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Produk</label>

            <textarea
                name="description"
                id="description"
                rows="4"
                style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
            >{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="specifications">Spesifikasi (format JSON)</label>

            <textarea
                name="specifications"
                id="specifications"
                rows="5"
                placeholder='{"Warna":"Hitam","Garansi":"1 tahun"}'
                style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
            >{{ old('specifications', $product->specifications ? json_encode($product->specifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        </div>

        <div class="form-group">
            <label for="price">Harga (Rupiah)</label>

            <input
                type="number"
                name="price"
                id="price"
                value="{{ old('price', $product->price) }}"
                min="0"
                step="0.01"
                required
            >
        </div>

        <div class="form-group">
            <label for="stock">Stok</label>

            <input
                type="number"
                name="stock"
                id="stock"
                value="{{ old('stock', $product->stock ?? 0) }}"
                min="0"
                required
            >
        </div>

        <div class="form-group">
            <label for="image">Gambar Produk</label>

            @if ($product->image_path)
                <img
                    src="{{ asset('storage/' . $product->image_path) }}"
                    alt="{{ $product->name }}"
                    width="130"
                    style="display:block; margin-bottom:12px; border-radius:8px;"
                >
            @endif

            <input
                type="file"
                name="image"
                id="image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <small style="color:#737b89;">
                JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
            </small>
        </div>

        <div class="form-group">
            <input type="hidden" name="is_active" value="0">

            <label class="checkbox-group">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $product->exists ? $product->is_active : true))
                >
                Produk aktif dan dapat ditampilkan.
            </label>
        </div>

        <div style="display:flex; gap:10px;">
            <button
                type="submit"
                class="button"
                @disabled($categories->isEmpty())
            >
                Simpan Produk
            </button>

            <a href="{{ route('admin.products.index') }}" class="button" style="background:#64748b;">
                Kembali
            </a>
        </div>
    </form>

</div>
@endsection

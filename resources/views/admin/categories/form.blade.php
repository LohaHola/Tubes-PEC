@extends('layouts.app')

@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="auth-card" style="max-width:600px;">

    <h1>{{ $category->exists ? 'Edit Kategori' : 'Tambah Kategori' }}</h1>

    <p class="subtitle">Lengkapi informasi kategori produk.</p>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
        method="POST"
    >
        @csrf

        @if ($category->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="name">Nama Kategori</label>

            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $category->name) }}"
                placeholder="Contoh: Elektronik"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>

            <textarea
                name="description"
                id="description"
                rows="5"
                style="width:100%; padding:12px; border:1px solid #d5d9e0; border-radius:7px;"
            >{{ old('description', $category->description) }}</textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="button">
                Simpan
            </button>

            <a href="{{ route('admin.categories.index') }}" class="button" style="background:#64748b;">
                Kembali
            </a>
        </div>
    </form>

</div>
@endsection

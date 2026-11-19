@extends('layouts.app')

@section('title', 'Kelola Produk - TubesPEC')

@section('content')

@include('layouts.partials.back-link', ['href' => route('admin.dashboard'), 'label' => 'Kembali ke Dashboard'])
<div class="dashboard-card">

    <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:25px;">
        <div>
            <h1>Kelola Produk</h1>
            <p class="subtitle">Atur informasi, harga, dan stok produk.</p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="button">
            + Tambah Produk
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.products.index') }}"
          style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama atau SKU..."
            style="flex:1; min-width:180px;"
        >

        <select name="category_id" style="padding:10px; border:1px solid #d5d9e0; border-radius:7px;">
            <option value="">Semua Kategori</option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected(request('category_id') == $category->id)
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="button">Cari</button>

        <a href="{{ route('admin.products.index') }}" class="button" style="background:#64748b;">
            Reset
        </a>
    </form>

    <div style="overflow-x:auto;">
        <table width="100%" cellpadding="12" style="border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f5f6f8;">
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($products as $product)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px; min-width:160px;">
                                @if ($product->image_path)
                                    <img
                                        src="{{ asset('storage/' . $product->image_path) }}"
                                        alt="{{ $product->name }}"
                                        width="48"
                                        height="48"
                                        style="object-fit:cover; border-radius:6px;"
                                    >
                                @endif

                                {{ $product->name }}
                            </div>
                        </td>

                        <td>{{ $product->sku ?? '-' }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </td>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <a href="{{ route('admin.products.edit', $product) }}">Edit</a>

                                <form
                                    action="{{ route('admin.products.destroy', $product) }}"
                                    method="POST"
                                    onsubmit="return confirm('Hapus produk ini?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" style="border:0; background:none; color:#dc2626; cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            Belum ada produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $products->links() }}
    </div>

</div>
@endsection

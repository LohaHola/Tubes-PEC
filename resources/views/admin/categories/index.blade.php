
@extends('layouts.app')

@section('title', 'Kelola Kategori - TubesPEC')

@section('content')
<div class="dashboard-card">

    <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:25px;">
        <div>
            <h1>Kelola Kategori</h1>
            <p class="subtitle">Atur kategori produk toko.</p>
        </div>

        <a href="{{ route('admin.categories.create') }}" class="button">
            + Tambah Kategori
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div style="overflow-x:auto;">
        <table width="100%" cellpadding="13" style="border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f5f6f8;">
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Jumlah Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($categories as $category)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>

                                <form
                                    action="{{ route('admin.categories.destroy', $category) }}"
                                    method="POST"
                                    onsubmit="return confirm('Hapus kategori ini?')"
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
                        <td colspan="5" style="text-align:center;">
                            Belum ada kategori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $categories->links() }}
    </div>

</div>
@endsection

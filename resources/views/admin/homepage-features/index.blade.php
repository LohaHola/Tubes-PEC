@extends('layouts.app')

@section('title', 'Promo & Rekomendasi - Arbyte')

@section('content')
@include('layouts.partials.back-link', ['href' => route('admin.dashboard'), 'label' => 'Kembali ke Dashboard'])

<div class="dashboard-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:24px;">
        <div>
            <h1>Promo & Rekomendasi</h1>
            <p class="subtitle">Atur produk dan konten yang tampil di halaman utama toko.</p>
        </div>
        <a href="{{ route('admin.homepage-features.create') }}" class="button">+ Tambah Konten</a>
    </div>

    @if ($features->isNotEmpty())
        <div style="overflow-x:auto;">
            <table width="100%" cellpadding="12" style="border-collapse:collapse;text-align:left;">
                <thead><tr style="background:#f5f6f8;">
                    <th>Jenis</th><th>Produk</th><th>Judul Section</th><th>Urutan</th><th>Status</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @foreach ($features as $feature)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td>{{ $feature->type === 'promo' ? 'Promo' : 'Rekomendasi' }}</td>
                        <td>{{ $feature->product->name }}</td>
                        <td>{{ $feature->section_title ?: '-' }}</td>
                        <td>{{ $feature->sort_order }}</td>
                        <td>{{ $feature->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <a href="{{ route('admin.homepage-features.edit', $feature) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.homepage-features.destroy', $feature) }}" onsubmit="return confirm('Hapus konten ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="border:0;background:none;color:#dc2626;cursor:pointer;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">Belum ada promo atau rekomendasi. Section terkait tidak akan muncul di halaman utama.</div>
    @endif
</div>
@endsection

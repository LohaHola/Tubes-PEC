@extends('layouts.app')

@section('title', 'Admin Dashboard - TubesPEC')

@section('content')
<div class="dashboard-card">

    <h1>Admin Dashboard</h1>

    <p class="subtitle">
        Selamat datang, {{ Auth::user()->name }}.
        Kamu login sebagai administrator.
    </p>

    <p>
        Halaman ini nantinya menjadi pusat pengelolaan toko,
        termasuk produk, kategori, pesanan, dan pembayaran.
    </p>

    <strong>Role:</strong> Administrator

</div>
@endsection

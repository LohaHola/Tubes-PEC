@extends('layouts.app')

@section('title', 'Customer Dashboard - TubesPEC')

@section('content')
<div class="dashboard-card">

    <h1>Customer Dashboard</h1>

    <p class="subtitle">
        Selamat datang, {{ Auth::user()->name }}.
    </p>

    <p>
        Kamu berhasil login sebagai customer.
        Nantinya halaman ini akan menampilkan informasi akun,
        riwayat pesanan, dan keranjang belanja.
    </p>

    <strong>Role:</strong> Customer

</div>
@endsection

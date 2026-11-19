
@extends('layouts.app')

@section('title', 'Admin Dashboard - Arbyte')

@section('content')

<style>
    .admin-dashboard {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .admin-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .admin-heading h1 {
        margin: 0;
        font-size: 27px;
    }

    .admin-muted {
        color: #77808e;
        font-size: 14px;
        margin-top: 8px;
    }

    .admin-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border: 1px solid #e7eaf0;
        border-radius: 11px;
    }

    .stat-label {
        color: #737d8b;
        font-size: 13px;
        margin-bottom: 13px;
    }

    .stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #20242c;
        overflow-wrap: anywhere;
    }

    .stat-description {
        color: #89919d;
        font-size: 12px;
        margin-top: 8px;
    }

    .admin-panel {
        background: white;
        border: 1px solid #e7eaf0;
        border-radius: 11px;
        padding: 22px;
    }

    .panel-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .panel-heading h2 {
        margin: 0;
        font-size: 18px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .quick-action {
        border: 1px solid #e7eaf0;
        border-radius: 9px;
        padding: 18px;
        transition: background .2s, border-color .2s;
    }

    .quick-action:hover {
        background: #f8fafc;
        border-color: #aeb8c6;
    }

    .quick-action strong {
        display: block;
        margin-bottom: 8px;
        font-size: 15px;
    }

    .quick-action span {
        display: block;
        color: #77808e;
        font-size: 13px;
        line-height: 1.5;
    }

    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        white-space: nowrap;
    }

    .admin-table th {
        color: #78818e;
        background: #f8f9fb;
        font-size: 12px;
        font-weight: 600;
        padding: 13px;
    }

    .admin-table td {
        padding: 15px 13px;
        border-bottom: 1px solid #edf0f3;
        font-size: 13px;
    }

    .admin-table tr:last-child td {
        border-bottom: none;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-paid {
        color: #166534;
        background: #dcfce7;
    }

    .status-pending {
        color: #92400e;
        background: #fef3c7;
    }

    .status-failed {
        color: #991b1b;
        background: #fee2e2;
    }

    .status-other {
        color: #475569;
        background: #e9eef5;
    }

    .admin-empty {
        color: #89919d;
        padding: 35px 10px;
        text-align: center;
        font-size: 14px;
    }

    .admin-link {
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    @media (max-width: 850px) {
        .admin-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .admin-stats {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card {
            padding: 15px;
        }

        .stat-value {
            font-size: 21px;
        }

        .admin-panel {
            padding: 16px;
        }
    }
</style>

<div class="admin-dashboard">

    {{-- Page Heading --}}
    <section class="admin-heading">
        <div>
            <h1>Dashboard Admin</h1>

            <div class="admin-muted">
                Selamat datang, {{ Auth::user()->name }}.
                Berikut ringkasan aktivitas toko Arbyte.
            </div>
        </div>

        <span class="status-badge status-other">
            Administrator
        </span>
    </section>

    {{-- Statistics --}}
    <section class="admin-stats">

        <div class="stat-card">
            <div class="stat-label">Total Produk</div>

            <div class="stat-value">
                {{ number_format($stats['total_products']) }}
            </div>

            <div class="stat-description">
                {{ $stats['active_products'] }} produk aktif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Kategori</div>

            <div class="stat-value">
                {{ number_format($stats['total_categories']) }}
            </div>

            <div class="stat-description">
                Kategori produk toko
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pelanggan</div>

            <div class="stat-value">
                {{ number_format($stats['total_customers']) }}
            </div>

            <div class="stat-description">
                Total akun customer
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Pesanan</div>

            <div class="stat-value">
                {{ number_format($stats['total_orders']) }}
            </div>

            <div class="stat-description">
                Seluruh pesanan tercatat
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pendapatan</div>

            <div class="stat-value" style="font-size:22px;">
                Rp {{ number_format((float) $stats['total_revenue'], 0, ',', '.') }}
            </div>

            <div class="stat-description">
                Dari pesanan berstatus lunas
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Menunggu Pembayaran</div>

            <div class="stat-value">
                {{ number_format($stats['pending_payments']) }}
            </div>

            <div class="stat-description">
                Pesanan belum dibayar
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Stok Habis</div>

            <div class="stat-value" style="color:#dc2626;">
                {{ number_format($stats['out_of_stock']) }}
            </div>

            <div class="stat-description">
                Produk dengan stok nol
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Status Admin</div>

            <div class="stat-value" style="font-size:20px; color:#166534;">
                Aktif
            </div>

            <div class="stat-description">
                Sesi administrator berjalan
            </div>
        </div>

    </section>

    {{-- Quick Actions --}}
    <section class="admin-panel">

        <div class="panel-heading">
            <div>
                <h2>Akses Cepat</h2>

                <div class="admin-muted">
                    Pintasan untuk mengelola toko.
                </div>
            </div>
        </div>

        <div class="quick-actions">

            <a href="{{ route('admin.products.index') }}"
               class="quick-action">
                <strong>Kelola Produk &rarr;</strong>
                <span>Tambah, edit, hapus, dan atur stok produk.</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="quick-action">
                <strong>Kelola Kategori &rarr;</strong>
                <span>Atur kategori dan pengelompokan produk.</span>
            </a>

            <a href="{{ route('storefront.products.index') }}"
               class="quick-action">
                <strong>Lihat Toko &rarr;</strong>
                <span>Buka katalog yang dapat dilihat pelanggan.</span>
            </a>

            <a href="{{ route('admin.orders.index') }}"
                class="quick-action">
                    <strong>Kelola Pesanan &rarr;</strong>
                    <span>
                        Periksa pesanan pelanggan dan proses pengirimannya.
                    </span>
                </a>

        </div>
    </section>

    {{-- Recent Orders --}}
    <section class="admin-panel">

        <div class="panel-heading">
            <div>
                <h2>Pesanan Terbaru</h2>

                <div class="admin-muted">
                    Enam pesanan terakhir yang masuk ke sistem.
                </div>
            </div>
        </div>

        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Nomor Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recentOrders as $order)

                        @php
                            $paymentClass = match ($order->payment_status) {
                                'paid' => 'status-paid',
                                'pending' => 'status-pending',
                                'failed', 'expired', 'cancelled' => 'status-failed',
                                default => 'status-other',
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                            </td>

                            <td>
                                {{ $order->user?->name ?? 'Pengguna tidak tersedia' }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="status-badge {{ $paymentClass }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>

                            <td>
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5">
                                <div class="admin-empty">
                                    Belum ada pesanan yang masuk.
                                    Pesanan pelanggan nantinya akan tampil di sini.
                                </div>
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>

        </div>

    </section>

</div>

@endsection

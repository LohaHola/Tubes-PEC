
@extends('layouts.app')

@section('title', 'Customer Dashboard - Arbyte')

@section('content')

<style>
    .customer-dashboard {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .customer-welcome {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 28px;
    }

    .customer-welcome h1 {
        margin: 0;
        font-size: 27px;
    }

    .customer-muted {
        color: #77808e;
        font-size: 14px;
        margin-top: 9px;
        line-height: 1.6;
    }

    .customer-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
    }

    .customer-stat {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        padding: 20px;
    }

    .customer-stat-label {
        font-size: 13px;
        color: #77808e;
        margin-bottom: 12px;
    }

    .customer-stat-value {
        font-size: 25px;
        font-weight: bold;
        color: #20242c;
        overflow-wrap: anywhere;
    }

    .customer-panel {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        padding: 22px;
    }

    .customer-panel h2 {
        font-size: 19px;
        margin: 0 0 18px;
    }

    .customer-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .customer-action {
        display: block;
        padding: 19px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        transition: .2s;
    }

    .customer-action:hover {
        background: #f8fafc;
        border-color: #aeb8c6;
    }

    .customer-action strong {
        display: block;
        margin-bottom: 8px;
        font-size: 15px;
    }

    .customer-action span {
        color: #77808e;
        font-size: 13px;
        line-height: 1.5;
    }

    .customer-table-wrapper {
        overflow-x: auto;
    }

    .customer-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        white-space: nowrap;
    }

    .customer-table th {
        background: #f8f9fb;
        color: #77808e;
        font-size: 12px;
        padding: 13px;
    }

    .customer-table td {
        padding: 15px 13px;
        border-bottom: 1px solid #edf0f3;
        font-size: 13px;
    }

    .customer-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-paid {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-other {
        background: #e9eef5;
        color: #475569;
    }

    .customer-empty {
        padding: 35px 15px;
        text-align: center;
        color: #77808e;
        font-size: 14px;
    }

    @media (max-width: 800px) {
        .customer-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 500px) {
        .customer-stats {
            gap: 10px;
        }

        .customer-stat {
            padding: 15px;
        }

        .customer-stat-value {
            font-size: 21px;
        }

        .customer-actions {
            grid-template-columns: 1fr;
        }

        .customer-panel,
        .customer-welcome {
            padding: 18px;
        }
    }
</style>

<div class="customer-dashboard">

    {{-- Welcome --}}
    <section class="customer-welcome">

        <h1>
            Halo, {{ $user->name }}!
        </h1>

        <p class="customer-muted">
            Selamat datang di dashboard Arbyte.
            Pantau aktivitas belanja dan pesananmu di sini.
        </p>

        <a href="{{ route('storefront.products.index') }}"
           class="button">
            Mulai Belanja
        </a>

    </section>

    {{-- Statistics --}}
    <section class="customer-stats">

        <div class="customer-stat">
            <div class="customer-stat-label">
                Total Pesanan
            </div>

            <div class="customer-stat-value">
                {{ number_format($stats['total_orders']) }}
            </div>

        </div>

        <div class="customer-stat">
            <div class="customer-stat-label">
                Menunggu Pembayaran
            </div>

            <div class="customer-stat-value">
                {{ number_format($stats['pending_orders']) }}
            </div>

        </div>

        <div class="customer-stat">
            <div class="customer-stat-label">
                Item Keranjang
            </div>

            <div class="customer-stat-value">
                {{ number_format($stats['cart_quantity']) }}
            </div>

        </div>

        <div class="customer-stat">
            <div class="customer-stat-label">
                Total Belanja
            </div>

            <div class="customer-stat-value" style="font-size:21px;">
                Rp {{ number_format((float) $stats['total_spent'], 0, ',', '.') }}
            </div>

        </div>

    </section>

    {{-- Quick Actions --}}
    <section class="customer-panel">

        <h2>Akses Cepat</h2>

        <div class="customer-actions">

            <a href="{{ route('storefront.products.index') }}"
               class="customer-action">

                <strong>Jelajahi Produk &rarr;</strong>

                <span>
                    Temukan produk yang sesuai dengan kebutuhanmu.
                </span>

            </a>

            <a href="{{ route('cart.index') }}"
               class="customer-action">

                <strong>Keranjang Belanja &rarr;</strong>

                <span>
                    Lihat dan periksa produk yang ingin dibeli.
                </span>

            </a>

            <a href="{{ route('customer.profile.edit') }}"
               class="customer-action">

                <strong>Profil Saya &rarr;</strong>

                <span>
                    Perbarui data akun, nomor telepon, dan alamat pengiriman.
                </span>

            </a>

        </div>

    </section>

    {{-- Recent Orders --}}
    <section class="customer-panel">

        <h2>Pesanan Terbaru</h2>

        <div class="customer-table-wrapper">

            <table class="customer-table">

                <thead>
                    <tr>
                        <th>Nomor Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Pengiriman</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($recentOrders as $order)

                        @php
                            $paymentClass = match ($order->payment_status) {
                                'paid' => 'badge-paid',
                                'pending' => 'badge-pending',
                                'failed', 'expired', 'cancelled' => 'badge-failed',
                                default => 'badge-other',
                            };
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $order->order_number }}
                                </strong>
                            </td>

                            <td>
                                {{ $order->created_at->format('d M Y') }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="customer-badge {{ $paymentClass }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>

                            <td>
                                {{ ucfirst($order->fulfillment_status) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5">
                                <div class="customer-empty">

                                    <h3>Belum Ada Pesanan</h3>

                                    <p>
                                        Riwayat pesananmu akan muncul
                                        setelah melakukan pembelian.
                                    </p>

                                    <a href="{{ route('storefront.products.index') }}"
                                       class="button">
                                        Cari Produk
                                    </a>

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    {{-- Account Information --}}
    <section class="customer-panel">

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap;">
            <h2 style="margin:0;">Informasi Akun</h2>
            <a href="{{ route('customer.profile.edit') }}" class="admin-link">Edit Profil →</a>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">

            <div>
                <div class="customer-muted">Nama Lengkap</div>
                <strong>{{ $user->name }}</strong>
            </div>

            <div>
                <div class="customer-muted">Alamat Email</div>
                <strong>{{ $user->email }}</strong>
            </div>

            <div>
                <div class="customer-muted">Nomor Telepon</div>
                <strong>{{ $user->phone ?: 'Belum diisi' }}</strong>
            </div>

            <div>
                <div class="customer-muted">Alamat Pengiriman</div>
                <strong>{{ $user->address ? $user->address . ', ' . ($user->city ?? '') : 'Belum diisi' }}</strong>
            </div>

            <div>
                <div class="customer-muted">Tipe Akun</div>
                <strong>Customer</strong>
            </div>

            <div>
                <div class="customer-muted">Terdaftar Sejak</div>
                <strong>{{ $user->created_at->format('d M Y') }}</strong>
            </div>

        </div>

    </section>

</div>

@endsection

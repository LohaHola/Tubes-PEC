@extends('layouts.app')

@section('title', 'Manajemen Pesanan - Arbyte')

@section('content')

<style>
    .orders-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .orders-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .orders-stat {
        background: white;
        padding: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .orders-stat-label {
        color: #77808e;
        font-size: 13px;
    }

    .orders-stat-value {
        margin-top: 10px;
        font-size: 25px;
        font-weight: 700;
    }

    .orders-panel {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        padding: 22px;
    }

    .orders-table-wrapper {
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        white-space: nowrap;
    }

    .orders-table th {
        background: #f8f9fb;
        color: #77808e;
        font-size: 12px;
        padding: 14px;
    }

    .orders-table td {
        border-bottom: 1px solid #edf0f3;
        padding: 15px 14px;
        font-size: 13px;
    }

    .order-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .order-paid {
        background: #dcfce7;
        color: #166534;
    }

    .order-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .order-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .order-other {
        background: #e9eef5;
        color: #475569;
    }

    .orders-filter {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .orders-filter input,
    .orders-filter select {
        padding: 11px;
        border: 1px solid #d5d9e0;
        border-radius: 7px;
        font-size: 13px;
    }

    .orders-filter input {
        flex: 1;
        min-width: 180px;
    }

    @media (max-width: 750px) {
        .orders-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="orders-page">

    <div>
        <h1 style="margin-bottom:5px;">Manajemen Pesanan</h1>

        <p style="color:#77808e; font-size:14px;">
            Pantau pesanan pelanggan dan proses pemenuhan pesanan.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="orders-stats">

        <div class="orders-stat">
            <div class="orders-stat-label">Total Pesanan</div>
            <div class="orders-stat-value">
                {{ number_format($stats['total']) }}
            </div>
        </div>

        <div class="orders-stat">
            <div class="orders-stat-label">Menunggu Pembayaran</div>
            <div class="orders-stat-value">
                {{ number_format($stats['pending']) }}
            </div>
        </div>

        <div class="orders-stat">
            <div class="orders-stat-label">Sudah Dibayar</div>
            <div class="orders-stat-value">
                {{ number_format($stats['paid']) }}
            </div>
        </div>

        <div class="orders-stat">
            <div class="orders-stat-label">Sedang Diproses</div>
            <div class="orders-stat-value">
                {{ number_format($stats['processing']) }}
            </div>
        </div>

    </section>

    <section class="orders-panel">

        <h2 style="font-size:19px; margin-top:0;">
            Daftar Pesanan
        </h2>

        <form action="{{ route('admin.orders.index') }}"
              method="GET"
              class="orders-filter">

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nomor pesanan, nama, atau email..."
            >

            <select name="payment_status">
                <option value="">Semua Pembayaran</option>

                @foreach (['pending', 'paid', 'failed', 'expired', 'cancelled', 'refunded'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('payment_status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <select name="fulfillment_status">
                <option value="">Semua Pengiriman</option>

                @foreach (['unprocessed', 'processing', 'shipped', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}"
                        @selected(request('fulfillment_status') === $status)>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="button">
                Filter
            </button>

            <a href="{{ route('admin.orders.index') }}"
               class="button"
               style="background:#64748b;">
                Reset
            </a>

        </form>

        <div class="orders-table-wrapper">

            <table class="orders-table">

                <thead>
                    <tr>
                        <th>Nomor Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($orders as $order)

                        @php
                            $paymentClass = match ($order->payment_status) {
                                'paid' => 'order-paid',
                                'pending' => 'order-pending',
                                'failed', 'expired', 'cancelled' => 'order-failed',
                                default => 'order-other',
                            };

                            $fulfillmentClass = match ($order->fulfillment_status) {
                                'completed', 'shipped' => 'order-paid',
                                'processing' => 'order-pending',
                                default => 'order-other',
                            };
                        @endphp

                        <tr>

                            <td>
                                <strong>{{ $order->order_number }}</strong>
                            </td>

                            <td>
                                <div>{{ $order->user?->name ?? 'Pengguna tidak tersedia' }}</div>
                                <span style="font-size:11px; color:#89919d;">
                                    {{ $order->user?->email ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="order-badge {{ $paymentClass }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>

                            <td>
                                <span class="order-badge {{ $fulfillmentClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   style="color:#334155; font-weight:600;">
                                    Detail &rarr;
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" style="text-align:center; padding:35px;">
                                Belum ada pesanan yang tercatat.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div style="margin-top:20px;">
            {{ $orders->links() }}
        </div>

    </section>

</div>

@endsection

@extends('layouts.storefront')

@section('title', 'Pesanan Saya - TubesPEC')

@section('content')

<h1>Pesanan Saya</h1>

<p style="color:#77808e; margin-bottom:25px;">
    Lihat status dan riwayat pembelian kamu.
</p>

@if ($orders->isEmpty())

    <div class="empty-state">
        <h2>Belum Ada Pesanan</h2>
        <p>Pesanan yang kamu buat akan muncul di halaman ini.</p>

        <a href="{{ route('storefront.products.index') }}"
           class="store-button">
            Mulai Belanja
        </a>
    </div>

@else

    <div style="background:white; border:1px solid #e7eaf0; border-radius:11px; overflow:hidden;">

        <div style="overflow-x:auto;">
            <table width="100%" cellpadding="15"
                   style="border-collapse:collapse; text-align:left; white-space:nowrap;">

                <thead style="background:#f8f9fb;">
                    <tr>
                        <th>Nomor Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($orders as $order)

                        @php
                            $statusClass = match ($order->payment_status) {
                                'paid' => 'status-paid',
                                'pending' => 'status-pending',
                                'failed', 'expired', 'cancelled' => 'status-failed',
                                default => 'status-other',
                            };
                        @endphp

                        <tr style="border-top:1px solid #edf0f3;">

                            <td>
                                <strong>{{ $order->order_number }}</strong>
                            </td>

                            <td>
                                {{ $order->created_at->format('d M Y H:i') }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>

                            <td>
                                {{ ucfirst($order->fulfillment_status) }}
                            </td>

                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}"
                                   class="admin-link">
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>
        </div>

        <div style="padding:15px;">
            {{ $orders->links() }}
        </div>

    </div>

@endif

@endsection

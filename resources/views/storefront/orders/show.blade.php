@extends('layouts.storefront')

@section('title', 'Detail Pesanan - TubesPEC')

@section('content')

@include('layouts.partials.back-link', ['href' => route('customer.orders.index'), 'label' => 'Kembali ke Pesanan Saya'])

<div style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:24px; margin-bottom:20px;">

    <div style="display:flex; justify-content:space-between; gap:15px; flex-wrap:wrap;">

        <div>
            <div style="color:#77808e; font-size:13px;">
                Nomor Pesanan
            </div>

            <h1 style="font-size:23px; margin:8px 0;">
                {{ $order->order_number }}
            </h1>

            <div style="color:#77808e; font-size:13px;">
                Dibuat pada {{ $order->created_at->format('d M Y, H:i') }}
            </div>
        </div>

        <span class="status-badge
            {{ $order->payment_status === 'paid' ? 'status-paid' :
                ($order->payment_status === 'pending' ? 'status-pending' :
                (in_array($order->payment_status, ['failed', 'expired', 'cancelled']) ? 'status-failed' : 'status-other')) }}"
            style="align-self:flex-start;">
            {{ ucfirst($order->payment_status) }}
        </span>

    </div>

    <div style="margin-top:25px; padding-top:20px; border-top:1px solid #edf0f3;">

        <h2 style="font-size:17px;">Status Pesanan</h2>

        <p style="color:#64748b; font-size:14px;">
            {{ ucfirst($order->fulfillment_status) }}
        </p>
        @if (
            $order->payment_status === 'pending' &&
            $order->inventory_status === 'reserved' &&
            $order->reserved_until?->isFuture()
        )

            <div style="margin-top:22px; padding:18px; background:#fffbeb; border:1px solid #fde68a; border-radius:9px;">

                <strong>Pesanan Menunggu Pembayaran</strong>

                <p style="font-size:13px; color:#92400e; line-height:1.6;">
                    Selesaikan pembayaran sebelum
                    {{ $order->reserved_until->format('d M Y, H:i') }}.
                    Setelah batas waktu tersebut, reservasi stok dapat dilepaskan.
                </p>

                <form
                    action="{{ route('customer.orders.pay', $order) }}"
                    method="POST"
                >
                    @csrf

                    <button type="submit" class="store-button">
                        Bayar Sekarang
                    </button>
                </form>

            </div>

        @endif

        @if ($order->inventory_status === 'reserved' && $order->reserved_until)
            <p style="color:#92400e; font-size:13px;">
                Batas waktu reservasi stok:
                {{ $order->reserved_until->format('d M Y, H:i') }}
            </p>
        @endif

    </div>

</div>

<div style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:24px; margin-bottom:20px;">

    <h2 style="font-size:19px; margin-top:0;">
        Produk yang Dipesan
    </h2>

    @foreach ($order->items as $item)

        <div style="display:flex; gap:15px; padding:15px 0; border-bottom:1px solid #edf0f3;">

            @if ($item->product_image_path)
                <img
                    src="{{ asset('storage/' . $item->product_image_path) }}"
                    alt="{{ $item->product_name }}"
                    style="width:75px; height:75px; object-fit:cover; border-radius:8px;"
                >
            @else
                <div class="image-placeholder"
                     style="width:75px; height:75px; border-radius:8px;">
                    No Image
                </div>
            @endif

            <div style="flex:1;">
                <strong>{{ $item->product_name }}</strong>

                <div style="color:#77808e; font-size:12px; margin-top:5px;">
                    SKU: {{ $item->product_sku ?? '-' }}
                </div>

                <div style="color:#77808e; font-size:13px; margin-top:5px;">
                    {{ $item->quantity }} ×
                    Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                </div>
            </div>

            <strong style="font-size:14px; white-space:nowrap;">
                Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
            </strong>

        </div>

    @endforeach

    <div style="margin-top:20px; max-width:360px; margin-left:auto;">

        <div style="display:flex; justify-content:space-between; margin:12px 0;">
            <span style="color:#77808e;">Subtotal</span>
            <strong>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</strong>
        </div>

        <div style="display:flex; justify-content:space-between; margin:12px 0;">
            <span style="color:#77808e;">Ongkos Kirim</span>
            <strong>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</strong>
        </div>

        <div style="display:flex; justify-content:space-between; border-top:1px solid #e7eaf0; padding-top:17px; margin-top:17px;">
            <strong>Total Pesanan</strong>
            <strong style="font-size:19px;">
                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
            </strong>
        </div>

    </div>

</div>

<div style="background:white; border:1px solid #e7eaf0; border-radius:11px; padding:24px;">

    <h2 style="font-size:19px; margin-top:0;">
        Informasi Pengiriman
    </h2>

    <p style="margin-bottom:7px;">
        <strong>{{ $order->recipient_name }}</strong>
    </p>

    <p style="color:#64748b; font-size:14px; line-height:1.7;">
        {{ $order->recipient_phone }}<br>
        {{ $order->recipient_address }}<br>
        {{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}
    </p>

    @if ($order->notes)
        <div style="margin-top:18px;">
            <strong>Catatan Pesanan</strong>
            <p style="color:#64748b; font-size:14px;">
                {{ $order->notes }}
            </p>
        </div>
    @endif

</div>

@endsection

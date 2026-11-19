@extends('layouts.app')

@section('title', 'Detail Pesanan - TubesPEC')

@section('content')

<style>
    .order-detail {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-detail-panel {
        background: white;
        padding: 23px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
    }

    .order-detail-heading {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .order-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .order-detail-label {
        color: #77808e;
        font-size: 13px;
        margin-bottom: 7px;
    }

    .order-detail-value {
        font-size: 14px;
        font-weight: 600;
    }

    .order-detail-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .order-detail-table th {
        background: #f8f9fb;
        color: #77808e;
        font-size: 12px;
        padding: 13px;
    }

    .order-detail-table td {
        padding: 14px 13px;
        border-bottom: 1px solid #edf0f3;
        font-size: 13px;
    }

    .order-detail-table-wrapper {
        overflow-x: auto;
    }

    .order-update-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .order-update-form select {
        padding: 11px;
        border: 1px solid #d5d9e0;
        border-radius: 7px;
        min-width: 190px;
    }

    @media (max-width: 600px) {
        .order-detail-grid {
            grid-template-columns: 1fr;
        }

        .order-detail-panel {
            padding: 17px;
        }
    }
</style>

<div class="order-detail">

    @include('layouts.partials.back-link', ['href' => route('admin.orders.index'), 'label' => 'Kembali ke Daftar Pesanan'])

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Order Header --}}
    <section class="order-detail-panel">

        <div class="order-detail-heading">
            <div>
                <div class="order-detail-label">Nomor Pesanan</div>

                <h1 style="font-size:24px; margin:5px 0 8px;">
                    {{ $order->order_number }}
                </h1>

                <div class="order-detail-label">
                    Dibuat pada {{ $order->created_at->format('d M Y, H:i') }}
                </div>
            </div>

            <span class="order-badge
                {{ $order->payment_status === 'paid' ? 'order-paid' :
                    ($order->payment_status === 'pending' ? 'order-pending' :
                    (in_array($order->payment_status, ['failed', 'expired', 'cancelled']) ? 'order-failed' : 'order-other')) }}">
                {{ ucfirst($order->payment_status) }}
            </span>
        </div>

        <div class="order-detail-grid" style="margin-top:25px;">

            <div>
                <div class="order-detail-label">Nama Pelanggan</div>
                <div class="order-detail-value">
                    {{ $order->user?->name ?? 'Pengguna tidak tersedia' }}
                </div>
            </div>

            <div>
                <div class="order-detail-label">Email</div>
                <div class="order-detail-value">
                    {{ $order->user?->email ?? '-' }}
                </div>
            </div>

            <div>
                <div class="order-detail-label">Status Pengiriman</div>
                <div class="order-detail-value">
                    {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
                </div>
            </div>

            <div>
                <div class="order-detail-label">Status Inventaris</div>
                <div class="order-detail-value">
                    {{ ucfirst($order->inventory_status) }}
                </div>
            </div>

        </div>

    </section>

    {{-- Fulfillment Management --}}
    <section class="order-detail-panel">

        <h2 style="font-size:19px; margin-top:0;">
            Pengelolaan Pengiriman
        </h2>

        @if ($order->payment_status !== 'paid')

            <div class="alert" style="background:#fef3c7; color:#92400e;">
                Pesanan belum dibayar. Admin belum dapat memproses pengiriman.
            </div>

        @elseif (!empty($nextStatuses))

            <p style="color:#77808e; font-size:13px;">
                Perbarui status sesuai perkembangan proses pesanan.
            </p>

            <form
                action="{{ route('admin.orders.update', $order) }}"
                method="POST"
                class="order-update-form"
            >
                @csrf
                @method('PATCH')

                <select name="fulfillment_status" required>
                    <option value="">Pilih status selanjutnya</option>

                    @foreach ($nextStatuses as $status)
                        <option value="{{ $status }}">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="button"
                        onclick="return confirm('Perbarui status pengiriman pesanan?')">
                    Perbarui Status
                </button>

            </form>

        @else

            <div style="color:#77808e; font-size:14px;">
                Pesanan sudah mencapai status akhir atau tidak memiliki
                tahapan pengiriman berikutnya.
            </div>

        @endif

    </section>

    {{-- Ordered Products --}}
    <section class="order-detail-panel">

        <h2 style="font-size:19px; margin-top:0;">
            Produk yang Dipesan
        </h2>

        <div class="order-detail-table-wrapper">

            <table class="order-detail-table">

                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>SKU</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($order->items as $item)

                        <tr>

                            <td>
                                <div style="display:flex; align-items:center; gap:10px; min-width:150px;">

                                    @if ($item->product_image_path)
                                        <img
                                            src="{{ asset('storage/' . $item->product_image_path) }}"
                                            alt="{{ $item->product_name }}"
                                            width="48"
                                            height="48"
                                            style="object-fit:cover; border-radius:6px;"
                                        >
                                    @endif

                                    <strong>{{ $item->product_name }}</strong>

                                </div>
                            </td>

                            <td>{{ $item->product_sku ?? '-' }}</td>

                            <td>
                                Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>

                            <td>{{ $item->quantity }}</td>

                            <td>
                                Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div style="max-width:350px; margin:22px 0 0 auto;">

            <div style="display:flex; justify-content:space-between; margin:12px 0;">
                <span class="order-detail-label">Subtotal</span>
                <strong>
                    Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}
                </strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin:12px 0;">
                <span class="order-detail-label">Ongkos Kirim</span>
                <strong>
                    Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}
                </strong>
            </div>

            <div style="display:flex; justify-content:space-between; border-top:1px solid #e5e7eb; padding-top:17px;">
                <strong>Total Pesanan</strong>

                <strong style="font-size:19px;">
                    Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                </strong>
            </div>

        </div>

    </section>

    {{-- Shipping Address --}}
    <section class="order-detail-panel">

        <h2 style="font-size:19px; margin-top:0;">
            Alamat Pengiriman
        </h2>

        <div class="order-detail-grid">

            <div>
                <div class="order-detail-label">Nama Penerima</div>
                <div class="order-detail-value">
                    {{ $order->recipient_name }}
                </div>
            </div>

            <div>
                <div class="order-detail-label">Nomor Telepon</div>
                <div class="order-detail-value">
                    {{ $order->recipient_phone }}
                </div>
            </div>

            <div style="grid-column:1 / -1;">
                <div class="order-detail-label">Alamat Lengkap</div>
                <div class="order-detail-value" style="line-height:1.7;">
                    {{ $order->recipient_address }}
                </div>
            </div>

            <div>
                <div class="order-detail-label">Kota/Kabupaten</div>
                <div class="order-detail-value">{{ $order->city }}</div>
            </div>

            <div>
                <div class="order-detail-label">Provinsi</div>
                <div class="order-detail-value">{{ $order->province }}</div>
            </div>

            <div>
                <div class="order-detail-label">Kode Pos</div>
                <div class="order-detail-value">{{ $order->postal_code }}</div>
            </div>

            @if ($order->notes)
                <div style="grid-column:1 / -1;">
                    <div class="order-detail-label">Catatan</div>
                    <div class="order-detail-value">{{ $order->notes }}</div>
                </div>
            @endif

        </div>

    </section>

    {{-- Payment Attempts --}}
    <section class="order-detail-panel">

        <h2 style="font-size:19px; margin-top:0;">
            Riwayat Pembayaran
        </h2>

        @forelse ($order->payments as $payment)

            <div style="padding:15px 0; border-bottom:1px solid #edf0f3;">

                <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">

                    <strong>{{ $payment->provider }}</strong>

                    <span class="order-badge order-other">
                        {{ ucfirst($payment->status) }}
                    </span>

                </div>

                <div class="order-detail-label" style="margin-top:9px;">
                    Nominal:
                    Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                </div>

                <div class="order-detail-label">
                    Metode:
                    {{ $payment->payment_method ?? '-' }}
                </div>

                @if ($payment->provider_transaction_id)
                    <div class="order-detail-label">
                        ID Transaksi:
                        {{ $payment->provider_transaction_id }}
                    </div>
                @endif

                @if ($payment->paid_at)
                    <div class="order-detail-label">
                        Dibayar pada:
                        {{ $payment->paid_at->format('d M Y, H:i') }}
                    </div>
                @endif

                <div class="order-detail-label">
                    Notifikasi tercatat: {{ $payment->notifications->count() }}
                </div>

            </div>

        @empty

            <div style="color:#77808e; font-size:14px;">
                Belum ada percobaan pembayaran. Integrasi pembayaran akan ditambahkan pada tahap berikutnya.
            </div>

        @endforelse

    </section>

</div>

@endsection

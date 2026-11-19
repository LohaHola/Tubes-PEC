@extends('layouts.app')

@section('title', 'Overview — Arbyte Admin')

@section('content')
<div class="admin-dashboard-v2">
    <section class="admin-hero-v2">
        <div>
            <div class="admin-eyebrow">ARBYTE / BUSINESS INTELLIGENCE</div>
            <h1>Selamat datang, {{ Auth::user()->name }}</h1>
            <p>Pantau operasional toko, performa penjualan, dan aktivitas pesanan dari satu tempat.</p>
        </div>
        <div class="admin-date-chip">
            <span class="admin-live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </section>

    <section class="admin-kpi-grid">
        <article class="admin-kpi-card">
            <div class="admin-kpi-top"><span>Omzet bulan ini</span><span class="admin-kpi-icon">↗</span></div>
            <strong>Rp {{ number_format((float) $stats['month_revenue'], 0, ',', '.') }}</strong>
            <small>
                @if ($stats['revenue_growth'] !== null)
                    <span class="{{ $stats['revenue_growth'] >= 0 ? 'metric-positive' : 'metric-negative' }}">
                        {{ $stats['revenue_growth'] >= 0 ? '+' : '' }}{{ number_format($stats['revenue_growth'], 1, ',', '.') }}%
                    </span>
                    dibanding bulan lalu
                @else
                    Belum ada data pembanding bulan lalu
                @endif
            </small>
        </article>
        <article class="admin-kpi-card">
            <div class="admin-kpi-top"><span>Total pesanan</span><span class="admin-kpi-icon">▤</span></div>
            <strong>{{ number_format($stats['total_orders']) }}</strong>
            <small>{{ number_format($stats['pending_payments']) }} menunggu pembayaran</small>
        </article>
        <article class="admin-kpi-card">
            <div class="admin-kpi-top"><span>Rata-rata transaksi</span><span class="admin-kpi-icon">◎</span></div>
            <strong>Rp {{ number_format((float) $stats['average_order_value'], 0, ',', '.') }}</strong>
            <small>Dihitung dari pesanan yang sudah dibayar</small>
        </article>
        <article class="admin-kpi-card">
            <div class="admin-kpi-top"><span>Total customer</span><span class="admin-kpi-icon">♙</span></div>
            <strong>{{ number_format($stats['total_customers']) }}</strong>
            <small>{{ number_format($stats['active_products']) }} produk aktif di katalog</small>
        </article>
    </section>

    <section class="admin-dashboard-grid">
        <article class="admin-analytics-panel admin-revenue-panel">
            <div class="admin-panel-head">
                <div><span class="admin-eyebrow">FINANCIAL PERFORMANCE</span><h2>Performa omzet</h2><p>Ringkasan pendapatan 12 bulan terakhir</p></div>
                <span class="admin-panel-tag">12 bulan</span>
            </div>
            <div class="admin-month-bars">
                @foreach ($monthlyPerformance as $month)
                    <div class="admin-month-row">
                        <span>{{ $month['label'] }}</span>
                        <div class="admin-bar-track"><div class="admin-bar-fill" style="width: {{ min(100, ($month['revenue'] / $maxMonthlyRevenue) * 100) }}%"></div></div>
                        <strong>Rp {{ number_format($month['revenue'], 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="admin-finance-note">
                <span>ⓘ</span>
                <p>Angka ini merupakan omzet dari pesanan berstatus lunas, belum dikurangi harga modal, biaya operasional, atau pajak. Laba bersih belum dapat dihitung karena data HPP belum tersedia.</p>
            </div>
        </article>

        <article class="admin-analytics-panel admin-tracker-panel">
            <div class="admin-panel-head">
                <div><span class="admin-eyebrow">ORDER OPERATIONS</span><h2>Order tracker</h2><p>Posisi pesanan saat ini</p></div>
                <a href="{{ route('admin.orders.index') }}">Semua pesanan ↗</a>
            </div>
            <div class="admin-tracker-list">
                @foreach ($orderTracker as $step)
                    <div class="admin-tracker-item">
                        <span class="admin-tracker-dot tone-{{ $step['tone'] }}"></span>
                        <span>{{ $step['label'] }}</span>
                        <strong>{{ number_format($step['count']) }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="admin-tracker-footer">
                <div><strong>{{ number_format($stats['completed_orders']) }}</strong><span>Pesanan selesai</span></div>
                <div><strong>{{ number_format($stats['processing_orders'] + $stats['shipped_orders']) }}</strong><span>Dalam pemenuhan</span></div>
            </div>
        </article>
    </section>

    <section class="admin-analytics-panel admin-timeline-panel">
        <div class="admin-panel-head">
            <div><span class="admin-eyebrow">STORE ACTIVITY</span><h2>Timeline jumlah order</h2><p>Jumlah pesanan masuk dalam 14 hari terakhir</p></div>
            <span class="admin-panel-tag">14 hari terakhir</span>
        </div>
        <div class="admin-timeline-chart">
            @foreach ($dailyTimeline as $day)
                <div class="admin-timeline-column" title="{{ $day['label'] }}: {{ $day['orders'] }} pesanan">
                    <strong>{{ $day['orders'] }}</strong>
                    <div class="admin-timeline-track"><div class="admin-timeline-bar" style="height: {{ max(4, ($day['orders'] / $maxDailyOrders) * 100) }}%"></div></div>
                    <span>{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="admin-bottom-grid">
        <article class="admin-analytics-panel">
            <div class="admin-panel-head">
                <div><span class="admin-eyebrow">LIVE WORKLOAD</span><h2>Ringkasan operasional</h2><p>Indikator kondisi toko saat ini</p></div>
            </div>
            <div class="admin-ops-grid">
                <div><span>Produk terdaftar</span><strong>{{ number_format($stats['total_products']) }}</strong></div>
                <div><span>Kategori</span><strong>{{ number_format($stats['total_categories']) }}</strong></div>
                <div><span>Stok habis</span><strong class="{{ $stats['out_of_stock'] > 0 ? 'metric-negative' : 'metric-positive' }}">{{ number_format($stats['out_of_stock']) }}</strong></div>
                <div><span>Total omzet</span><strong>Rp {{ number_format((float) $stats['total_revenue'], 0, ',', '.') }}</strong></div>
            </div>
            <a class="admin-outline-link" href="{{ route('admin.products.index') }}">Periksa inventaris produk ↗</a>
        </article>

        <article class="admin-analytics-panel">
            <div class="admin-panel-head">
                <div><span class="admin-eyebrow">CUSTOMER RELATIONSHIP</span><h2>Customer management</h2><p>Kelola data dan riwayat pelanggan</p></div>
            </div>
            <div class="admin-customer-callout">
                <div class="admin-customer-count">{{ number_format($stats['total_customers']) }}</div>
                <div><strong>Customer terdaftar</strong><span>Akses profil, alamat, kontak, dan histori transaksi.</span></div>
            </div>
            <a class="admin-primary-link" href="{{ route('admin.customers.index') }}">Buka manajemen customer <span>→</span></a>
        </article>
    </section>

    <section class="admin-analytics-panel admin-recent-panel">
        <div class="admin-panel-head">
            <div><span class="admin-eyebrow">LATEST ACTIVITY</span><h2>Pesanan terbaru</h2><p>Delapan pesanan terakhir yang tercatat</p></div>
            <a href="{{ route('admin.orders.index') }}">Lihat semua ↗</a>
        </div>
        <div class="admin-table-scroll">
            <table class="admin-modern-table">
                <thead><tr><th>Pesanan</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>Pembayaran</th><th>Pengiriman</th><th></th></tr></thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->user?->name ?? 'Customer tidak tersedia' }}</td>
                            <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                            <td><span class="admin-status-pill status-{{ $order->payment_status === 'paid' ? 'green' : ($order->payment_status === 'pending' ? 'amber' : 'muted') }}">{{ match($order->payment_status) { 'paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', default => ucfirst($order->payment_status) } }}</span></td>
                            <td>{{ match($order->fulfillment_status) { 'unprocessed' => 'Belum diproses', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', default => ucfirst($order->fulfillment_status) } }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="admin-row-link">Detail →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="admin-empty-state">Belum ada pesanan masuk. Pesanan baru akan muncul di sini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

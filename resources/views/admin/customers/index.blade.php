@extends('layouts.app')

@section('title', 'Customer Management — Arbyte')

@section('content')
<div class="admin-page-v2">
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">CUSTOMER RELATIONSHIP</span><h1>Manajemen Customer</h1><p>Temukan pelanggan, lihat aktivitas transaksi, dan perbarui informasi akun.</p></div>
        <span class="admin-panel-tag">{{ number_format($stats['total']) }} akun</span>
    </div>

    <div class="admin-customer-stats">
        <div><span>Total customer</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div><span>Sudah pernah bertransaksi</span><strong>{{ number_format($stats['with_orders']) }}</strong></div>
        <div><span>Terdaftar bulan ini</span><strong>{{ number_format($stats['new_this_month']) }}</strong></div>
    </div>

    <section class="admin-analytics-panel">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="admin-search-form">
            <div><label for="customer-search">Cari customer</label><input id="customer-search" type="search" name="q" value="{{ request('q') }}" placeholder="Nama, email, atau nomor telepon..."></div>
            <button type="submit" class="admin-primary-link">Cari customer</button>
            @if (request()->filled('q'))<a href="{{ route('admin.customers.index') }}" class="admin-clear-filter">Reset</a>@endif
        </form>

        <div class="admin-table-scroll">
            <table class="admin-modern-table">
                <thead><tr><th>Customer</th><th>Kontak</th><th>Bergabung</th><th>Pesanan</th><th>Total dibayar</th><th></th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="admin-customer-cell">
                                    <div class="admin-customer-avatar">
                                        @if ($customer->profile_photo_path)
                                            <img src="{{ asset('storage/' . $customer->profile_photo_path) }}" alt="">
                                        @else
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div><strong>{{ $customer->name }}</strong><small>#{{ $customer->id }}</small></div>
                                </div>
                            </td>
                            <td><span>{{ $customer->email }}</span><small class="admin-cell-sub">{{ $customer->phone ?: 'Nomor telepon belum diisi' }}</small></td>
                            <td>{{ $customer->created_at->format('d M Y') }}</td>
                            <td>{{ number_format($customer->orders_count) }}</td>
                            <td>Rp {{ number_format((float) ($customer->paid_total ?? 0), 0, ',', '.') }}</td>
                            <td><a href="{{ route('admin.customers.show', $customer) }}" class="admin-row-link">Lihat detail →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty-state">Tidak ada customer yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $customers->links() }}</div>
    </section>
</div>
@endsection

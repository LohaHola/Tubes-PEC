<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransPaymentSynchronizer;
use App\Services\OrderCancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('storefront.orders.index', compact('orders'));
    }

    public function show(
        Request $request,
        Order $order,
        MidtransPaymentSynchronizer $synchronizer
    ): View {
        abort_unless($request->user()->role === 'customer', 403);

        abort_unless(
            $order->user_id === $request->user()->id,
            404
        );

        // Recover a paid transaction if Midtrans' HTTP notification was delayed
        // or could not reach the local Ngrok/Laragon endpoint.
        if ($order->payment_status === 'pending') {
            $synchronizer->syncPaidStatus($order);
            $order->refresh();
        }

        $order->load('items');

        return view('storefront.orders.show', compact('order'));
    }

    public function cancel(
        Request $request,
        Order $order,
        OrderCancellationService $cancellations
    ): RedirectResponse {
        abort_unless($request->user()->role === 'customer', 403);

        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            404
        );

        $result = $cancellations->cancel(
            (int) $order->id,
            (int) $request->user()->id
        );

        return match ($result) {
            'cancelled' => redirect()
                ->route('customer.orders.show', $order)
                ->with('status', 'Pesanan berhasil dibatalkan. Stok produk telah dikembalikan.'),

            'payment_processing' => redirect()
                ->route('customer.orders.show', $order)
                ->with('error', 'Pembayaran sedang diproses. Tunggu sebentar, lalu coba batalkan kembali.'),

            'provider_cancel_failed' => redirect()
                ->route('customer.orders.show', $order)
                ->with('error', 'Pembayaran belum dapat dibatalkan di Midtrans. Status pesanan tidak diubah dan stok tetap ditahan.'),

            default => redirect()
                ->route('customer.orders.show', $order)
                ->with('error', 'Pesanan ini tidak dapat dibatalkan karena sudah dibayar, diproses, atau reservasi stoknya tidak aktif.'),
        };
    }
}

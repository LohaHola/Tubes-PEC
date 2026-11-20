<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class OrderCancellationService
{
    /**
     * Cancel a customer's unpaid order and release its reserved stock.
     *
     * Returns one of: cancelled, not_cancellable, payment_processing,
     * or provider_cancel_failed.
     */
    public function cancel(int $orderId, int $customerId): string
    {
        return DB::transaction(function () use ($orderId, $customerId) {
            $order = Order::whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $order->user_id !== $customerId) {
                abort(404);
            }

            if (
                $order->payment_status !== 'pending' ||
                $order->fulfillment_status !== 'unprocessed' ||
                $order->inventory_status !== 'reserved'
            ) {
                return 'not_cancellable';
            }

            // Do not race the external Snap creation request.
            $payments = Payment::where('order_id', $order->id)
                ->where('provider', 'midtrans')
                ->whereIn('status', ['creating', 'pending'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($payments->contains(fn (Payment $payment) => $payment->status === 'creating')) {
                return 'payment_processing';
            }

            // Cancel every still-pending Midtrans payment attempt before releasing stock.
            foreach ($payments as $payment) {
                $providerStatus = $this->cancelMidtransPayment($payment);

                if ($providerStatus === null) {
                    return 'provider_cancel_failed';
                }

                $metadata = $payment->metadata ?? [];
                $metadata['cancelled_by_customer_at'] = now()->toIso8601String();

                $payment->update([
                    'status' => 'cancelled',
                    'provider_status' => $providerStatus,
                    'metadata' => $metadata,
                ]);
            }

            // Restore only stock that this order had previously reserved.
            $items = $order->items()
                ->whereNotNull('product_id')
                ->orderBy('product_id')
                ->get();

            foreach ($items as $item) {
                $product = Product::withTrashed()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'payment_status' => 'cancelled',
                'fulfillment_status' => 'cancelled',
                'inventory_status' => 'released',
            ]);

            return 'cancelled';
        }, attempts: 3);
    }

    /**
     * Return a provider status only after Midtrans confirms the payment
     * session/transaction can no longer be paid.
     */
    private function cancelMidtransPayment(Payment $payment): ?string
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            return null;
        }

        $isProduction = (bool) config('midtrans.is_production');
        $snapBaseUrl = $isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $apiBaseUrl = $isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        // First revoke the Snap session. Midtrans documents this endpoint
        // with the raw merchant server key in the Authorization header.
        if ($payment->snap_token) {
            try {
                $sessionResponse = Http::withHeaders([
                    'Authorization' => $serverKey,
                    'Accept' => 'application/json',
                ])->timeout(10)->post(
                    $snapBaseUrl . '/snap/v1/transactions/' .
                    rawurlencode($payment->snap_token) . '/cancel'
                );

                if ($sessionResponse->successful()) {
                    return 'snap_session_cancelled';
                }

                $messages = $sessionResponse->json('error_messages', []);
                $message = strtolower(
                    is_array($messages)
                        ? implode(' ', $messages)
                        : (string) $sessionResponse->body()
                );

                if (str_contains($message, 'token already canceled')) {
                    return 'snap_session_cancelled';
                }

                // "Transaction is on progress" means a payment transaction
                // already exists; continue with the transaction cancel API.
            } catch (Throwable $exception) {
                // Try the transaction endpoint below before failing closed.
            }
        }

        if (!$payment->provider_order_id) {
            return null;
        }

        $transactionUrl = $apiBaseUrl . '/v2/' .
            rawurlencode($payment->provider_order_id);

        try {
            $cancelResponse = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post($transactionUrl . '/cancel');

            $cancelStatus = strtolower(
                (string) $cancelResponse->json('transaction_status', '')
            );
            $cancelMessage = strtolower(
                (string) $cancelResponse->json('status_message', '')
            );

            if (
                $cancelResponse->successful() &&
                (
                    $cancelStatus === 'cancel' ||
                    str_contains($cancelMessage, 'transaction is canceled')
                )
            ) {
                return 'cancel';
            }

            // A failed cancel request may mean the transaction already
            // expired/cancelled, or that it was paid. Verify before deciding.
            $statusResponse = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(10)
                ->get($transactionUrl . '/status');

            if (!$statusResponse->successful()) {
                return null;
            }

            $currentStatus = strtolower(
                (string) $statusResponse->json('transaction_status', '')
            );

            if (in_array($currentStatus, ['cancel', 'expire', 'deny', 'failure'], true)) {
                return $currentStatus;
            }
        } catch (Throwable $exception) {
            return null;
        }

        // Pending/settled/unknown statuses are not safe to release locally.
        return null;
    }
}

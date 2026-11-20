<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransPaymentSynchronizer
{
    /**
     * Recover a missing/delayed webhook by checking the authoritative
     * Midtrans transaction status. Only confirmed successful payments
     * are synchronized by this fallback.
     */
    public function syncPaidStatus(Order $order): string
    {
        if ($order->payment_status !== 'pending') {
            return 'not_pending';
        }

        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            Log::warning('Midtrans status sync skipped: server key is not configured.', [
                'order_id' => $order->order_number,
            ]);

            return 'not_configured';
        }

        $signatureSecretKey = (string) config('midtrans.signature_key');

        if ($signatureSecretKey === '') {
            Log::warning('Midtrans status sync skipped: signature secret key is not configured.', [
                'order_id' => $order->order_number,
            ]);

            return 'signature_secret_not_configured';
        }

        $payment = $order->payments()
            ->where('provider', 'midtrans')
            ->whereNotNull('provider_order_id')
            ->latest('id')
            ->first();

        if (!$payment) {
            return 'payment_not_found';
        }

        $isProduction = (bool) config('midtrans.is_production');
        $apiBaseUrl = $isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(8)
                ->get(
                    $apiBaseUrl . '/v2/' .
                    rawurlencode($payment->provider_order_id) . '/status'
                );
        } catch (Throwable $exception) {
            Log::warning('Midtrans status sync request failed.', [
                'order_id' => $order->order_number,
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            return 'provider_unavailable';
        }

        // A Snap transaction can return 404 before the customer selects
        // a payment method. Keep the local order pending in that case.
        if (!$response->successful()) {
            return 'provider_status_unavailable';
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            return 'invalid_provider_response';
        }

        $providerOrderId = (string) ($payload['order_id'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));
        $currency = strtoupper((string) ($payload['currency'] ?? 'IDR'));

        $providerAmount = $this->amountToCents($grossAmount);
        $localAmount = $this->amountToCents($payment->amount);

        if (
            $providerOrderId !== $payment->provider_order_id ||
            $currency !== 'IDR' ||
            $providerAmount === null ||
            $localAmount === null ||
            $providerAmount !== $localAmount
        ) {
            Log::warning('Midtrans status sync response did not match the local payment.', [
                'order_id' => $order->order_number,
                'payment_id' => $payment->id,
            ]);

            return 'provider_response_mismatch';
        }

        $expectedSignature = hash(
            'sha512',
            $providerOrderId . $statusCode . $grossAmount . $signatureSecretKey
        );

        if (
            empty($payload['signature_key']) ||
            !hash_equals($expectedSignature, (string) $payload['signature_key'])
        ) {
            Log::warning('Midtrans status sync signature is invalid.', [
                'order_id' => $order->order_number,
                'payment_id' => $payment->id,
            ]);

            return 'invalid_signature';
        }

        $isConfirmedSuccess = (
            $statusCode === '200' &&
            in_array($transactionStatus, ['settlement', 'capture'], true) &&
            ($fraudStatus === '' || $fraudStatus === 'accept')
        );

        if (!$isConfirmedSuccess) {
            return 'not_paid';
        }

        return DB::transaction(function () use (
            $order,
            $payment,
            $payload,
            $transactionStatus,
            $fraudStatus,
            $grossAmount,
            $statusCode
        ) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'paid') {
                return 'already_paid';
            }

            // Do not undo refunds or other terminal states during reconciliation.
            if (in_array($lockedOrder->payment_status, ['refunded', 'failed', 'expired'], true)) {
                return 'terminal_order_status';
            }

            $notificationKey = hash('sha256', implode('|', [
                'status-sync',
                (string) $payload['order_id'],
                (string) ($payload['transaction_id'] ?? ''),
                $statusCode,
                $grossAmount,
                $transactionStatus,
                $fraudStatus,
            ]));

            PaymentNotification::firstOrCreate(
                ['notification_key' => $notificationKey],
                [
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' => $payload['transaction_id'] ?? null,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus ?: null,
                    'signature_valid' => true,
                    'processing_status' => 'processed_status_sync',
                    'received_at' => now(),
                    'processed_at' => now(),
                ]
            );

            $metadata = $lockedPayment->metadata ?? [];
            $metadata['latest_status_sync'] = [
                'transaction_status' => $transactionStatus,
                'checked_at' => now()->toIso8601String(),
            ];

            $lockedPayment->update([
                'provider_transaction_id' => $payload['transaction_id'] ?? $lockedPayment->provider_transaction_id,
                'payment_method' => $payload['payment_type'] ?? $lockedPayment->payment_method,
                'provider_status' => $transactionStatus,
                'fraud_status' => $fraudStatus ?: null,
                'status' => 'paid',
                'paid_at' => $lockedPayment->paid_at ?? now(),
                'metadata' => $metadata,
            ]);

            $needsManualReview = $lockedOrder->inventory_status !== 'reserved';

            $lockedOrder->update([
                'payment_status' => 'paid',
                'inventory_status' => $lockedOrder->inventory_status === 'reserved'
                    ? 'committed'
                    : $lockedOrder->inventory_status,
            ]);

            if ($needsManualReview) {
                Log::warning('Midtrans confirmed a paid order whose stock reservation was already released.', [
                    'order_id' => $lockedOrder->order_number,
                    'payment_id' => $lockedPayment->id,
                    'inventory_status' => $lockedOrder->inventory_status,
                ]);
            }

            return $needsManualReview ? 'paid_needs_manual_review' : 'paid';
        }, attempts: 5);
    }

    private function amountToCents(string|int|float $amount): ?int
    {
        $value = trim((string) $amount);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;

class PaymentController extends Controller
{
    public function pay(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);
        abort_unless($order->user_id === $request->user()->id, 404);

        if (!config('midtrans.server_key')) {
            return back()->with('error', 'Konfigurasi Midtrans belum lengkap.');
        }

        $amount = $this->toWholeRupiah($order->total_amount);

        if ($amount === null) {
            return back()->with(
                'error',
                'Nominal pembayaran harus berupa rupiah bulat tanpa desimal.'
            );
        }

        try {
            $payment = DB::transaction(function () use ($order, $request) {
                $lockedOrder = Order::whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedOrder->user_id !== $request->user()->id) {
                    abort(404);
                }

                if (
                    $lockedOrder->payment_status !== 'pending' ||
                    $lockedOrder->inventory_status !== 'reserved' ||
                    !$lockedOrder->reserved_until ||
                    !$lockedOrder->reserved_until->isFuture()
                ) {
                    abort(redirect()->route('customer.orders.show', $lockedOrder)
                        ->with('error', 'Pesanan tidak lagi tersedia untuk pembayaran.'));
                }

                // Gunakan pembayaran pending yang masih aktif jika sudah ada.
                $existingPayment = $lockedOrder->payments()
                    ->where('provider', 'midtrans')
                    ->where('status', 'pending')
                    ->whereNotNull('redirect_url')
                    ->where('expired_at', '>', now())
                    ->latest()
                    ->first();

                if ($existingPayment) {
                    return $existingPayment;
                }

                $attempt = $lockedOrder->payments()
                    ->where('provider', 'midtrans')
                    ->count() + 1;

                $suffix = '-P' . str_pad(
                    (string) $attempt,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                $providerOrderId = substr(
                    $lockedOrder->order_number,
                    0,
                    50 - strlen($suffix)
                ) . $suffix;

                return $lockedOrder->payments()->create([
                    'provider' => 'midtrans',
                    'provider_order_id' => $providerOrderId,
                    'amount' => $lockedOrder->total_amount,
                    'currency' => 'IDR',
                    'status' => 'creating',
                    'expired_at' => $lockedOrder->reserved_until,
                    'metadata' => [
                        'attempt' => $attempt,
                    ],
                ]);
            }, attempts: 5);
        } catch (Throwable $e) {
            throw $e;
        }

        // Pengguna dapat melanjutkan Snap yang sebelumnya sudah dibuat.
        if ($payment->status === 'pending' && $payment->redirect_url) {
            return redirect()->away($payment->redirect_url);
        }

        $order->load('user', 'items');

        $remainingSeconds = (int) now()->diffInSeconds(
            $order->reserved_until,
            false
        );

        $durationMinutes = (int) floor($remainingSeconds / 60);

        if ($durationMinutes < 1) {
            $payment->update([
                'status' => 'request_failed',
                'provider_status' => 'reservation_too_short',
            ]);

            return back()->with(
                'error',
                'Waktu reservasi stok hampir habis. Silakan buat pesanan baru.'
            );
        }

        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$clientKey = config('midtrans.client_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');

            // Idempotensi untuk percobaan pembuatan transaksi.
            Config::$paymentIdempotencyKey = $payment->provider_order_id;

            if (config('midtrans.notification_url')) {
                Config::$overrideNotifUrl = config('midtrans.notification_url');
            } else {
                Config::$overrideNotifUrl = null;
            }

            $nameParts = preg_split(
                '/\s+/',
                trim($order->recipient_name),
                2
            );

            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? '';

            $params = [
                'transaction_details' => [
                    'order_id' => $payment->provider_order_id,
                    'gross_amount' => $this->toWholeRupiah(
                        $order->total_amount
                    ),
                ],

                'credit_card' => [
                    'secure' => true,
                ],

                'customer_details' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $order->user->email,
                    'phone' => $order->recipient_phone,

                    'shipping_address' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $order->recipient_phone,
                        'address' => $order->recipient_address,
                        'city' => $order->city,
                        'postal_code' => $order->postal_code,
                        'country_code' => 'IDN',
                    ],
                ],

                'expiry' => [
                    'start_time' => now()
                        ->setTimezone('Asia/Jakarta')
                        ->format('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => $durationMinutes,
                ],

                'callbacks' => [
                    'finish' => route('payment.finish'),
                ],
            ];

            $snapResponse = Snap::createTransaction($params);

            $redirectUrl = $snapResponse->redirect_url ?? null;
            $snapToken = $snapResponse->token ?? null;

            $expectedHost = config('midtrans.is_production')
                ? 'app.midtrans.com'
                : 'app.sandbox.midtrans.com';

            $actualHost = $redirectUrl
                ? parse_url($redirectUrl, PHP_URL_HOST)
                : null;

            if (
                !$snapToken ||
                !$redirectUrl ||
                $actualHost !== $expectedHost
            ) {
                throw new \RuntimeException(
                    'Respons Snap tidak sesuai dengan environment.'
                );
            }

            $payment->update([
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'status' => 'pending',
                'provider_status' => 'pending',
                'metadata' => [
                    'attempt' => $payment->metadata['attempt'] ?? null,
                    'snap_created_at' => now()->toIso8601String(),
                ],
            ]);

            return redirect()->away($redirectUrl);

        } catch (Throwable $e) {
            $payment->update([
                'status' => 'request_failed',
                'provider_status' => 'request_failed',
            ]);

            Log::error('Midtrans Snap transaction creation failed.', [
                'order_id' => $order->order_number,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Gagal membuat halaman pembayaran. Silakan coba kembali.'
            );
        }
    }

    public function finish(Request $request): RedirectResponse
    {
        $providerOrderId = (string) $request->query('order_id');

        $payment = Payment::where('provider', 'midtrans')
            ->where('provider_order_id', $providerOrderId)
            ->firstOrFail();

        $order = Order::findOrFail($payment->order_id);

        abort_unless(
            $order->user_id === $request->user()->id,
            404
        );

        return redirect()
            ->route('customer.orders.show', $order)
            ->with(
                'status',
                'Kamu kembali dari halaman Midtrans. Status pembayaran akan diperbarui melalui notifikasi server.'
            );
    }

    public function notification(Request $request): JsonResponse
    {
        // Midtrans notification signatures are calculated with the merchant Server Key.
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            return response()->json([
                'message' => 'Payment configuration unavailable.',
            ], 503);
        }

        $payload = $request->all();

        $requiredFields = [
            'order_id',
            'status_code',
            'gross_amount',
            'signature_key',
            'transaction_status',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                return response()->json([
                    'message' => 'Incomplete notification.',
                ], 422);
            }
        }

        // Verifikasi signature menggunakan Server Key sesuai rumus resmi Midtrans.
        $expectedSignature = hash(
            'sha512',
            (string) $payload['order_id'] .
            (string) $payload['status_code'] .
            (string) $payload['gross_amount'] .
            $serverKey
        );

        $signatureValid = hash_equals(
            $expectedSignature,
            (string) $payload['signature_key']
        );

        if (!$signatureValid) {
            Log::warning('Midtrans notification signature invalid.');

            return response()->json([
                'message' => 'Invalid signature.',
            ], 403);
        }

        $payment = Payment::where('provider', 'midtrans')
            ->where('provider_order_id', $payload['order_id'])
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found.',
            ], 404);
        }

        if (
            isset($payload['currency']) &&
            strtoupper($payload['currency']) !== 'IDR'
        ) {
            return response()->json([
                'message' => 'Unsupported currency.',
            ], 422);
        }

        $notificationAmount = $this->amountToCents(
            $payload['gross_amount']
        );

        $paymentAmount = $this->amountToCents($payment->amount);

        if (
            $notificationAmount === null ||
            $paymentAmount === null ||
            $notificationAmount !== $paymentAmount
        ) {
            Log::warning('Midtrans notification amount mismatch.', [
                'provider_order_id' => $payload['order_id'],
            ]);

            return response()->json([
                'message' => 'Transaction amount mismatch.',
            ], 422);
        }

        // ID unik untuk setiap perubahan status dari Midtrans.
        $notificationKey = hash('sha256', implode('|', [
            (string) $payload['order_id'],
            (string) ($payload['transaction_id'] ?? ''),
            (string) $payload['status_code'],
            (string) $payload['gross_amount'],
            (string) $payload['transaction_status'],
            (string) ($payload['fraud_status'] ?? ''),
        ]));

        $result = DB::transaction(function () use (
            $payment,
            $payload,
            $notificationKey
        ) {
            // Urutan lock konsisten: order, payment, produk.
            $order = Order::whereKey($payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $notification = PaymentNotification::firstOrCreate(
                [
                    'notification_key' => $notificationKey,
                ],
                [
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' =>
                        $payload['transaction_id'] ?? null,
                    'transaction_status' =>
                        $payload['transaction_status'],
                    'fraud_status' => $payload['fraud_status'] ?? null,
                    'signature_valid' => true,
                    'processing_status' => 'received',
                    'received_at' => now(),
                ]
            );

            // Notifikasi berulang tidak memproses stok dua kali.
            if (!$notification->wasRecentlyCreated) {
                return 'duplicate';
            }

            $transactionStatus = strtolower(
                (string) $payload['transaction_status']
            );

            $fraudStatus = strtolower(
                (string) ($payload['fraud_status'] ?? '')
            );

            if ($fraudStatus === 'deny') {
                $transactionStatus = 'deny';
            }

            $paymentStatus = null;
            $orderPaymentStatus = null;
            $processingStatus = 'processed';

            switch ($transactionStatus) {
                case 'settlement':
                case 'capture':
                    // A payment is successful only with status code 200.
                    // Some payment channels omit fraud_status; when supplied,
                    // Midtrans requires it to be ACCEPT.
                    if (
                        (string) $payload['status_code'] === '200' &&
                        ($fraudStatus === '' || $fraudStatus === 'accept')
                    ) {
                        $paymentStatus = 'paid';
                        $orderPaymentStatus = 'paid';
                    } else {
                        $paymentStatus = 'pending';
                        $processingStatus = 'awaiting_payment_confirmation';
                    }
                    break;

                case 'pending':
                case 'authorize':
                    $paymentStatus = 'pending';
                    break;

                case 'deny':
                case 'failure':
                    $paymentStatus = 'failed';
                    $orderPaymentStatus = 'failed';
                    break;

                case 'expire':
                    $paymentStatus = 'expired';
                    $orderPaymentStatus = 'expired';
                    break;

                case 'cancel':
                    $paymentStatus = 'cancelled';
                    $orderPaymentStatus = 'cancelled';
                    break;

                case 'refund':
                    $paymentStatus = 'refunded';
                    $orderPaymentStatus = 'refunded';
                    $processingStatus = 'needs_manual_review';
                    break;

                default:
                    $processingStatus = 'needs_manual_review';
                    break;
            }

            $currentOrderStatus = $order->payment_status;

            // Abaikan event pending yang datang terlambat setelah lunas.
            if (
                $currentOrderStatus === 'paid' &&
                in_array($transactionStatus, [
                    'pending',
                    'authorize',
                    'expire',
                    'failure',
                ], true)
            ) {
                $paymentStatus = $lockedPayment->status;
                $orderPaymentStatus = null;
                $processingStatus = 'ignored_stale';
            }

            // Jangan mengubah status refunded karena notifikasi lama.
            if (
                $currentOrderStatus === 'refunded' &&
                $transactionStatus !== 'refund'
            ) {
                $paymentStatus = $lockedPayment->status;
                $orderPaymentStatus = null;
                $processingStatus = 'ignored_stale';
            }

            // A late pending/expiry notification must not reopen a customer-cancelled order.
            // A genuine success is still recorded so funds are never silently ignored.
            $confirmedSuccessfulPayment = (
                (string) $payload['status_code'] === '200' &&
                in_array($transactionStatus, ['settlement', 'capture'], true) &&
                ($fraudStatus === '' || $fraudStatus === 'accept')
            );

            if (
                $currentOrderStatus === 'cancelled' &&
                !$confirmedSuccessfulPayment
            ) {
                $paymentStatus = $lockedPayment->status;
                $orderPaymentStatus = null;
                $processingStatus = 'ignored_cancelled_order';
            }

            $metadata = $lockedPayment->metadata ?? [];

            $metadata['latest_notification'] = [
                'transaction_status' => $transactionStatus,
                'transaction_time' => $payload['transaction_time'] ?? null,
                'status_code' => $payload['status_code'],
                'status_message' => $payload['status_message'] ?? null,
            ];

            $lockedPayment->update([
                'provider_transaction_id' =>
                    $payload['transaction_id'] ??
                    $lockedPayment->provider_transaction_id,

                'payment_method' =>
                    $payload['payment_type'] ??
                    $lockedPayment->payment_method,

                'provider_status' => $transactionStatus,
                'fraud_status' => $fraudStatus ?: null,
                'status' => $paymentStatus ?? $lockedPayment->status,

                'paid_at' => $paymentStatus === 'paid'
                    ? ($lockedPayment->paid_at ?? now())
                    : $lockedPayment->paid_at,

                'metadata' => $metadata,
            ]);

            // Pembayaran berhasil: stok yang direservasi menjadi committed.
            if ($orderPaymentStatus === 'paid') {
                $order->payment_status = 'paid';

                if ($order->inventory_status === 'reserved') {
                    $order->inventory_status = 'committed';
                } elseif ($order->inventory_status === 'released') {
                    // Dana masuk setelah reservasi sudah dilepaskan.
                    // Jangan mengambil ulang stok secara otomatis.
                    $processingStatus = 'needs_manual_review';
                }

                $order->save();
            }

            // Pembayaran gagal atau dibatalkan.
            if ($orderPaymentStatus && in_array(
                $orderPaymentStatus,
                ['failed', 'expired', 'cancelled'],
                true
            )) {
                if ($order->payment_status === 'paid') {
                    // Penolakan/reversal setelah pembayaran diterima.
                    if (
                        $order->fulfillment_status === 'shipped' ||
                        $order->fulfillment_status === 'completed'
                    ) {
                        $processingStatus = 'needs_manual_review';
                    }
                }

                $canRestoreStock = (
                    $order->inventory_status === 'reserved' ||
                    (
                        $order->payment_status === 'paid' &&
                        $order->inventory_status === 'committed' &&
                        in_array(
                            $order->fulfillment_status,
                            ['unprocessed', 'processing'],
                            true
                        )
                    )
                );

                if ($canRestoreStock) {
                    $this->restoreOrderStock($order);

                    $order->inventory_status = 'released';

                    if (!in_array(
                        $order->fulfillment_status,
                        ['shipped', 'completed'],
                        true
                    )) {
                        $order->fulfillment_status = 'cancelled';
                    }
                } elseif ($order->inventory_status === 'released') {
                    // Sudah dilepaskan oleh proses kedaluwarsa.
                } else {
                    $processingStatus = 'needs_manual_review';
                }

                $order->payment_status = $orderPaymentStatus;
                $order->save();
            }

            if ($orderPaymentStatus === 'refunded') {
                $order->payment_status = 'refunded';
                $order->save();
            }

            $notification->update([
                'processing_status' => $processingStatus,
                'processed_at' => now(),
            ]);

            return $processingStatus;
        }, attempts: 5);

        return response()->json([
            'message' => 'Notification received.',
            'result' => $result,
        ]);
    }

    private function restoreOrderStock(Order $order): void
    {
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
    }

    private function amountToCents(string|int|float $amount): ?int
    {
        $value = trim((string) $amount);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            return null;
        }

        [$whole, $fraction] = array_pad(
            explode('.', $value, 2),
            2,
            ''
        );

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

    private function toWholeRupiah(string|int|float $amount): ?int
    {
        $cents = $this->amountToCents($amount);

        if ($cents === null || $cents <= 0 || $cents % 100 !== 0) {
            return null;
        }

        return intdiv($cents, 100);
    }
}

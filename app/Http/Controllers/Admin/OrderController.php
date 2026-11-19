<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'payment_status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'paid',
                    'failed',
                    'expired',
                    'cancelled',
                    'refunded',
                ]),
            ],
            'fulfillment_status' => [
                'nullable',
                Rule::in([
                    'unprocessed',
                    'processing',
                    'shipped',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        $query = Order::with('user')->latest();

        if (!empty($validated['q'])) {
            $search = $validated['q'];

            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($validated['payment_status'])) {
            $query->where(
                'payment_status',
                $validated['payment_status']
            );
        }

        if (!empty($validated['fulfillment_status'])) {
            $query->where(
                'fulfillment_status',
                $validated['fulfillment_status']
            );
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('payment_status', 'pending')->count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'processing' => Order::where('fulfillment_status', 'processing')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items',
            'payments.notifications',
        ]);

        $nextStatuses = [];

        if ($order->payment_status === 'paid') {
            $nextStatuses = match ($order->fulfillment_status) {
                'unprocessed' => ['processing'],
                'processing' => ['shipped'],
                'shipped' => ['completed'],
                default => [],
            };
        }

        return view('admin.orders.show', compact(
            'order',
            'nextStatuses'
        ));
    }

    public function update(
        Request $request,
        Order $order
    ): RedirectResponse {
        $validated = $request->validate([
            'fulfillment_status' => [
                'required',
                Rule::in(['processing', 'shipped', 'completed']),
            ],
        ]);

        $result = DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status !== 'paid') {
                throw ValidationException::withMessages([
                    'fulfillment_status' =>
                        'Pesanan belum dibayar. Proses pengiriman belum dapat dilakukan.',
                ]);
            }

            $allowedTransitions = [
                'unprocessed' => ['processing'],
                'processing' => ['shipped'],
                'shipped' => ['completed'],
                'completed' => [],
                'cancelled' => [],
            ];

            $currentStatus = $lockedOrder->fulfillment_status;
            $newStatus = $validated['fulfillment_status'];

            if ($currentStatus === $newStatus) {
                return 'unchanged';
            }

            if (!in_array(
                $newStatus,
                $allowedTransitions[$currentStatus] ?? [],
                true
            )) {
                throw ValidationException::withMessages([
                    'fulfillment_status' =>
                        'Perubahan status pesanan tidak sesuai dengan alur yang diizinkan.',
                ]);
            }

            $lockedOrder->update([
                'fulfillment_status' => $newStatus,
            ]);

            return 'updated';
        }, attempts: 5);

        $message = $result === 'updated'
            ? 'Status pengiriman berhasil diperbarui.'
            : 'Status pengiriman tidak mengalami perubahan.';

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', $message);
    }
}

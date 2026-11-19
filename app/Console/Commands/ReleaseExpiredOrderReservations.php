<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredOrderReservations extends Command
{
    protected $signature = 'orders:release-expired';

    protected $description = 'Release stock from expired unpaid orders';

    public function handle(): int
    {
        $releasedCount = 0;

        Order::where('payment_status', 'pending')
            ->where('inventory_status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$releasedCount) {

                foreach ($orders as $candidate) {
                    $released = DB::transaction(function () use ($candidate) {

                        $order = Order::whereKey($candidate->id)
                            ->lockForUpdate()
                            ->first();

                        if (
                            !$order ||
                            $order->payment_status !== 'pending' ||
                            $order->inventory_status !== 'reserved' ||
                            !$order->reserved_until ||
                            $order->reserved_until->isFuture()
                        ) {
                            return false;
                        }

                        $items = $order->items()
                            ->orderBy('product_id')
                            ->get();

                        foreach ($items as $item) {
                            if (!$item->product_id) {
                                continue;
                            }

                            $product = Product::withTrashed()
                                ->whereKey($item->product_id)
                                ->lockForUpdate()
                                ->first();

                            if ($product) {
                                $product->increment('stock', $item->quantity);
                            }
                        }

                        $order->update([
                            'payment_status' => 'expired',
                            'fulfillment_status' => 'cancelled',
                            'inventory_status' => 'released',
                        ]);

                        return true;
                    }, attempts: 5);

                    if ($released) {
                        $releasedCount++;
                    }
                }
            });

        $this->info("Reservasi stok yang dilepaskan: {$releasedCount}");

        return self::SUCCESS;
    }
}

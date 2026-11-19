<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'rating.required' => 'Pilih rating untuk produk ini.',
            'review.min' => 'Ulasan minimal 10 karakter agar membantu pembeli lain.',
        ]);

        $order = Order::where('user_id', $request->user()->id)
            ->where('payment_status', 'paid')
            ->where('fulfillment_status', 'completed')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->latest()
            ->first();

        if (!$order) {
            throw ValidationException::withMessages([
                'review' => 'Kamu dapat memberi ulasan setelah pesanan produk ini selesai diterima.',
            ]);
        }

        ProductReview::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ],
            [
                'order_id' => $order->id,
                'rating' => $validated['rating'],
                'review' => $validated['review'],
            ]
        );

        return redirect()
            ->route('storefront.products.show', $product->slug)
            ->with('status', 'Terima kasih! Ulasan produk berhasil disimpan.');
    }
}

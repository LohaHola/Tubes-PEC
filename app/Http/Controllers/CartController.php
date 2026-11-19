<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cart->load('items.product.category');

        $subtotal = $cart->items->sum(function ($item) {
            if (!$item->product) {
                return 0;
            }

            return (float) $item->product->currentPrice() * $item->quantity;
        });

        return view('storefront.cart.index', compact(
            'cart',
            'subtotal'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($validated['product_id']);

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk sedang tidak tersedia.',
                ]);
            }

            $cart = Cart::firstOrCreate([
                'user_id' => $request->user()->id,
            ]);

            $item = $cart->items()
                ->where('product_id', $product->id)
                ->first();

            $newQuantity = ($item?->quantity ?? 0)
                + $validated['quantity'];

            if ($newQuantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah yang diminta melebihi stok tersedia. Stok saat ini: '.$product->stock,
                ]);
            }

            if ($item) {
                $item->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $newQuantity,
                ]);
            }
        });

        return redirect()
            ->route('cart.index')
            ->with('status', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        abort_unless(
            $cartItem->cart()
                ->where('user_id', $request->user()->id)
                ->exists(),
            404
        );

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($cartItem, $validated) {
            $item = CartItem::query()
                ->lockForUpdate()
                ->findOrFail($cartItem->id);

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($item->product_id);

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'quantity' => 'Produk sedang tidak tersedia.',
                ]);
            }

            if ($validated['quantity'] > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah melebihi stok tersedia. Stok saat ini: '.$product->stock,
                ]);
            }

            $item->update([
                'quantity' => $validated['quantity'],
            ]);
        });

        return redirect()
            ->route('cart.index')
            ->with('status', 'Jumlah produk berhasil diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        abort_unless(
            $cartItem->cart()
                ->where('user_id', $request->user()->id)
                ->exists(),
            404
        );

        $cartItem->delete();

        return redirect()
            ->route('cart.index')
            ->with('status', 'Produk berhasil dihapus dari keranjang.');
    }
}

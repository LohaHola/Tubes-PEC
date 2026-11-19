<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\IndonesiaRegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $cart = Cart::with('items.product.category')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kamu masih kosong.');
        }

        $subtotalCents = 0;

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                return redirect()->route('cart.index')
                    ->with('error', 'Ada produk yang sudah tidak tersedia. Periksa keranjangmu.');
            }

            if ($item->quantity > $product->stock) {
                return redirect()->route('cart.index')
                    ->with('error', 'Jumlah salah satu produk melebihi stok yang tersedia.');
            }

            $subtotalCents += $this->toCents($product->currentPrice()) * $item->quantity;
        }

        $shippingCostCents = (int) config('commerce.shipping_cost', 0) * 100;
        $totalCents = $subtotalCents + $shippingCostCents;

        return view('storefront.checkout.index', [
            'cart' => $cart,
            'subtotal' => $this->fromCents($subtotalCents),
            'shippingCost' => $this->fromCents($shippingCostCents),
            'total' => $this->fromCents($totalCents),
        ]);
    }

    public function store(Request $request, IndonesiaRegionService $regions): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_address' => ['required', 'string', 'max:2000'],
            'city' => ['required', 'string', 'max:100'],
            'province_id' => ['required', 'string', 'max:10'],
            'postal_code' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $provinceId = $validated['province_id'];
        $cityName = $validated['city'];

        try {
            $province = $regions->provinces()->first(
                fn (array $item) => $item['id'] === $provinceId
            );
            $city = $province ? $regions->findCity($provinceId, $cityName) : null;
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'city' => 'Data wilayah belum dapat diverifikasi. Coba lagi beberapa saat.',
            ]);
        }

        if (!$province || !$city) {
            throw ValidationException::withMessages([
                'city' => 'Kota/kabupaten tidak sesuai dengan provinsi yang dipilih.',
            ]);
        }

        $validated['province'] = $province['name'];
        $validated['city'] = $city['name'];
        unset($validated['province_id']);

        $order = DB::transaction(function () use ($request, $validated) {
            // Lock cart agar dua checkout dari akun yang sama
            // tidak memproses isi keranjang secara bersamaan.
            $cart = Cart::where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang tidak ditemukan.',
                ]);
            }

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang kamu masih kosong.',
                ]);
            }

            $preparedItems = [];
            $subtotalCents = 0;

            foreach ($cartItems as $cartItem) {
                $product = Product::whereKey($cartItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk sudah tidak tersedia.',
                    ]);
                }

                if ($cartItem->quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok produk '.$product->name.' tidak mencukupi.',
                    ]);
                }

                // Gunakan harga terbaru dari database.
                $unitPriceCents = $this->toCents($product->currentPrice());
                $lineTotalCents = $unitPriceCents * $cartItem->quantity;

                $subtotalCents += $lineTotalCents;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $this->fromCents($unitPriceCents),
                    'line_total' => $this->fromCents($lineTotalCents),
                ];
            }

            $shippingCostCents =
                (int) config('commerce.shipping_cost', 0) * 100;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-'.strtoupper((string) \Illuminate\Support\Str::ulid()),

                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'recipient_address' => $validated['recipient_address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'notes' => $validated['notes'] ?? null,

                'subtotal' => $this->fromCents($subtotalCents),
                'shipping_cost' => $this->fromCents($shippingCostCents),
                'total_amount' => $this->fromCents(
                    $subtotalCents + $shippingCostCents
                ),

                'payment_status' => 'pending',
                'fulfillment_status' => 'unprocessed',
                'inventory_status' => 'reserved',

                'reserved_until' => now()->addMinutes(
                    max(1, (int) config('commerce.order_reservation_minutes', 30))
                ),
            ]);

            foreach ($preparedItems as $item) {
                $product = $item['product'];

                // Stok dikurangi satu kali ketika reservasi dibuat.
                $product->decrement('stock', $item['quantity']);

                // Simpan snapshot agar riwayat transaksi tidak berubah
                // ketika informasi produk diedit di kemudian hari.
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_image_path' => $product->image_path,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
            }

            // Keranjang dikosongkan setelah pesanan berhasil dibuat.
            CartItem::where('cart_id', $cart->id)->delete();

            return $order;
        }, attempts: 5);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('status', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran sebelum batas reservasi berakhir.');
    }

    private function toCents(string|int|float $amount): int
    {
        $parts = explode('.', (string) $amount, 2);

        $whole = (int) ($parts[0] ?: 0);
        $fraction = str_pad($parts[1] ?? '0', 2, '0');

        return ($whole * 100) + (int) substr($fraction, 0, 2);
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad(
            (string) ($amount % 100),
            2,
            '0',
            STR_PAD_LEFT
        );
    }
}

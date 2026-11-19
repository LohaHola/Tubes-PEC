<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

    public function show(Request $request, Order $order): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        abort_unless(
            $order->user_id === $request->user()->id,
            404
        );

        $order->load('items');

        return view('storefront.orders.show', compact('order'));
    }
}

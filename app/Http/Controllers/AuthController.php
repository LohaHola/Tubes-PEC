<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('customer.dashboard');
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])
            ->onlyInput('email');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);

        $user->forceFill([
            'role' => 'customer',
        ])->save();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function dashboard(): RedirectResponse
    {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }

    public function adminDashboard(): View
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_orders' => Order::count(),
            'pending_payments' => Order::where('payment_status', 'pending')->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_revenue' => Order::where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentOrders'));
    }

    public function customerDashboard(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $cart = Cart::with('items')->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $ordersQuery = Order::where('user_id', $user->id);

        $stats = [
            'total_orders' => (clone $ordersQuery)->count(),

            'pending_orders' => (clone $ordersQuery)
                ->where('payment_status', 'pending')
                ->count(),

            'cart_quantity' => $cart->items->sum('quantity'),

            'total_spent' => (clone $ordersQuery)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        $recentOrders = (clone $ordersQuery)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.customer', compact(
            'user',
            'stats',
            'recentOrders'
        ));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Kamu berhasil logout.');
    }
}

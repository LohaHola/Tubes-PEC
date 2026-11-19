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
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonth();

        $paidOrders = Order::where('payment_status', 'paid');
        $currentMonthRevenue = (clone $paidOrders)
            ->whereBetween('created_at', [$monthStart, $now])
            ->sum('total_amount');
        $previousMonthRevenue = (clone $paidOrders)
            ->whereBetween('created_at', [
                $previousMonthStart,
                $monthStart->copy()->subSecond(),
            ])
            ->sum('total_amount');

        $revenueGrowth = $previousMonthRevenue > 0
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : null;

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_orders' => Order::count(),
            'pending_payments' => Order::where('payment_status', 'pending')->count(),
            'paid_orders' => (clone $paidOrders)->count(),
            'paid_unprocessed_orders' => (clone $paidOrders)->where('fulfillment_status', 'unprocessed')->count(),
            'processing_orders' => Order::where('fulfillment_status', 'processing')->count(),
            'shipped_orders' => Order::where('fulfillment_status', 'shipped')->count(),
            'completed_orders' => Order::where('fulfillment_status', 'completed')->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_revenue' => (clone $paidOrders)->sum('total_amount'),
            'month_revenue' => $currentMonthRevenue,
            'revenue_growth' => $revenueGrowth,
            'average_order_value' => (clone $paidOrders)->avg('total_amount') ?? 0,
        ];

        $monthlyPerformance = collect(range(11, 0))->map(function (int $monthsAgo) use ($now) {
            $start = $now->copy()->startOfMonth()->subMonths($monthsAgo);
            $end = $start->copy()->endOfMonth();

            return [
                'label' => $start->translatedFormat('M y'),
                'revenue' => (float) Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total_amount'),
                'orders' => Order::whereBetween('created_at', [$start, $end])->count(),
            ];
        });

        $maxMonthlyRevenue = max(1, (float) $monthlyPerformance->max('revenue'));

        $dailyTimeline = collect(range(13, 0))->map(function (int $daysAgo) use ($now) {
            $date = $now->copy()->startOfDay()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('d M'),
                'orders' => Order::whereDate('created_at', $date)->count(),
            ];
        });

        $maxDailyOrders = max(1, (int) $dailyTimeline->max('orders'));

        $orderTracker = [
            ['label' => 'Menunggu pembayaran', 'count' => $stats['pending_payments'], 'tone' => 'amber'],
            ['label' => 'Sudah dibayar', 'count' => $stats['paid_unprocessed_orders'], 'tone' => 'blue'],
            ['label' => 'Sedang diproses', 'count' => $stats['processing_orders'], 'tone' => 'violet'],
            ['label' => 'Dalam pengiriman', 'count' => $stats['shipped_orders'], 'tone' => 'cyan'],
            ['label' => 'Selesai', 'count' => $stats['completed_orders'], 'tone' => 'green'],
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'monthlyPerformance',
            'maxMonthlyRevenue',
            'dailyTimeline',
            'maxDailyOrders',
            'orderTracker',
            'recentOrders'
        ));
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

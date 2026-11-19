<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum([
                'orders as paid_total' => fn ($orders) => $orders->where('payment_status', 'paid'),
            ], 'total_amount')
            ->latest();

        if (!empty($validated['q'])) {
            $search = $validated['q'];
            $query->where(function ($users) use ($search) {
                $users->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'with_orders' => User::where('role', 'customer')->whereHas('orders')->count(),
            'new_this_month' => User::where('role', 'customer')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'customer', 404);

        $orders = $user->orders()->latest()->paginate(10);
        $totalSpent = $user->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return view('admin.customers.show', compact('user', 'orders', 'totalSpent'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return redirect()
            ->route('admin.customers.show', $user)
            ->with('status', 'Data customer berhasil diperbarui.');
    }
}

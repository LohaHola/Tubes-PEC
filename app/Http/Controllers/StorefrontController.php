<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        return view('storefront.home', compact('categories', 'products', 'promotions', 'recommendations'));
    }

    public function products(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $query = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true);

        if (!empty($validated['q'])) {
            $search = $validated['q'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['category'])) {
            $query->where('category_id', $validated['category']);
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('storefront.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'specifications']);

        $relatedProducts = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        $reviews = $product->reviews()->with('user')->latest()->paginate(5, ['*'], 'reviews_page');
        $reviewCount = $product->reviews()->count();
        $reviewAverage = (float) ($product->reviews()->avg('rating') ?? 0);

        $userReview = null;
        $canReview = false;

        if (auth()->check() && auth()->user()->role === 'customer') {
            $userReview = $product->reviews()->where('user_id', auth()->id())->first();
            $canReview = auth()->user()->orders()
                ->where('payment_status', 'paid')
                ->where('fulfillment_status', 'completed')
                ->whereHas('items', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();
        }

        return view('storefront.products.show', compact(
            'product',
            'relatedProducts',
            'reviews',
            'reviewCount',
            'reviewAverage',
            'userReview',
            'canReview'
        ));
    }
}

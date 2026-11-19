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
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        return view('storefront.home', compact(
            'categories',
            'products'
        ));
    }

    public function products(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $query = Product::with('category')
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

        $products = $query->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('storefront.products.index', compact(
            'products',
            'categories'
        ));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'specifications']);

        $relatedProducts = Product::with('category')
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('storefront.products.show', compact(
            'product',
            'relatedProducts'
        ));
    }
}

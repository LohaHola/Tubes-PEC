<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(10)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact(
            'products',
            'categories'
        ));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $specifications = $this->normalizeSpecifications(
            $validated['specifications'] ?? []
        );

        unset($validated['specifications']);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        unset($validated['image']);

        DB::transaction(function () use ($validated, $specifications) {
            $product = Product::create($validated);

            $product->specifications()->createMany($specifications);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }


    public function edit(Product $product): View
    {
        $product->load('specifications');

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $specifications = $this->normalizeSpecifications(
            $validated['specifications'] ?? []
        );

        unset($validated['specifications']);

        $validated['slug'] = $this->uniqueSlug(
            $validated['name'],
            $product->id
        );

        $validated['is_active'] = $request->boolean('is_active');

        $oldImage = $product->image_path;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')
                ->store('products', 'public');

            $validated['image_path'] = $newImage;
        }

        unset($validated['image']);

        DB::transaction(function () use ($product, $validated, $specifications) {
            $product->update($validated);

            $product->specifications()->delete();

            $product->specifications()->createMany($specifications);
        });

        if ($newImage && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array', 'max:30'],
            'specifications.*.name' => ['nullable', 'string', 'max:100'],
            'specifications.*.value' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        
    }

    private function normalizeSpecifications(array $specifications): array
    {
        return collect($specifications)
            ->map(fn ($item) => [
                'name' => trim($item['name'] ?? ''),
                'value' => trim($item['value'] ?? ''),
            ])
            ->filter(fn ($item) => $item['name'] !== '' && $item['value'] !== '')
            ->values()
            ->map(fn ($item, $index) => $item + [
                'sort_order' => $index,
            ])
            ->all();
    }
    
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $counter = 2;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageProductFeature;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class HomepageFeatureController extends Controller
{
    public function index(): View
    {
        $features = HomepageProductFeature::with('product.category')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('admin.homepage-features.index', compact('features'));
    }

    public function create(): View
    {
        return view('admin.homepage-features.form', [
            'feature' => new HomepageProductFeature(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($data['type'] === 'recommendation') $data['promo_price'] = null;
        HomepageProductFeature::create($data);

        return redirect()->route('admin.homepage-features.index')
            ->with('status', 'Konten homepage berhasil ditambahkan.');
    }

    public function edit(HomepageProductFeature $homepage_feature): View
    {
        return view('admin.homepage-features.form', [
            'feature' => $homepage_feature,
            'products' => Product::where('is_active', true)
                ->orWhere('id', $homepage_feature->product_id)
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, HomepageProductFeature $homepage_feature): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($data['type'] === 'recommendation') $data['promo_price'] = null;
        $homepage_feature->update($data);

        return redirect()->route('admin.homepage-features.index')
            ->with('status', 'Konten homepage berhasil diperbarui.');
    }

    public function destroy(HomepageProductFeature $homepage_feature): RedirectResponse
    {
        $homepage_feature->delete();

        return redirect()->route('admin.homepage-features.index')
            ->with('status', 'Konten homepage berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['promo', 'recommendation'])],
            'section_title' => ['nullable', 'string', 'max:120'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'item_title' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'promo_price' => ['nullable', 'numeric', 'min:0', 'exclude_if:type,recommendation'],
            'starts_at' => ['nullable', 'date', 'required_with:ends_at'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}

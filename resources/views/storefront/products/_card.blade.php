<a href="{{ route('storefront.products.show', $product->slug) }}"
   class="product-card">

    @if ($product->image_path)
        <img
            src="{{ asset('storage/' . $product->image_path) }}"
            alt="{{ $product->name }}"
            class="product-image"
        >
    @else
        <div class="image-placeholder">
            Tidak ada gambar
        </div>
    @endif

    <div class="product-info">

        <div class="product-category">
            {{ $product->category->name }}
        </div>

        <h3 class="product-name">
            {{ $product->name }}
        </h3>

        <div class="product-price">
            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
        </div>

        <div class="product-stock">
            @if ($product->stock > 0)
                Stok tersedia: {{ $product->stock }}
            @else
                Stok habis
            @endif
        </div>

    </div>
</a>
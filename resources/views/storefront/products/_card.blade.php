<a href="{{ route('storefront.products.show', $product->slug) }}" class="product-card">
    @if ($product->image_path)
        <img
            src="{{ asset('storage/' . $product->image_path) }}"
            alt="{{ $product->name }}"
            class="product-image"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="image-placeholder" role="img" aria-label="Gambar produk belum tersedia">
            Gambar belum tersedia
        </div>
    @endif

    <div class="product-info">
        <div class="product-category">{{ $product->category?->name ?? 'Technology' }}</div>
        <h3 class="product-name">{{ $product->name }}</h3>
        <div class="product-price">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>
        <div class="product-card-rating">
            @if (($product->reviews_count ?? 0) > 0)
                <span class="review-stars">★</span>
                <strong>{{ number_format((float) $product->reviews_avg_rating, 1) }}</strong>
                <span>({{ $product->reviews_count }})</span>
            @else
                <span>Belum ada ulasan</span>
            @endif
        </div>
        <div class="product-stock {{ $product->stock > 0 ? '' : 'is-unavailable' }}">
            @if ($product->stock > 0)
                Tersedia · {{ $product->stock }} unit
            @else
                Stok habis
            @endif
        </div>
    </div>
</a>
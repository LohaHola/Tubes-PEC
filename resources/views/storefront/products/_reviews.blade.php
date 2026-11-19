<section class="product-reviews" id="reviews">
    <div class="reviews-heading">
        <div>
            <div class="product-category">PENGALAMAN PEMBELI</div>
            <h2>Ulasan Produk</h2>
            <p class="section-subtitle">Pendapat dari customer yang sudah membeli produk ini.</p>
        </div>
        <div class="reviews-score">
            <strong>{{ $reviewCount ? number_format($reviewAverage, 1) : '—' }}</strong>
            <span class="review-stars" aria-label="Rata-rata {{ number_format($reviewAverage, 1) }} dari 5 bintang">
                @for ($star = 1; $star <= 5; $star++)
                    {{ $star <= round($reviewAverage) ? '★' : '☆' }}
                @endfor
            </span>
            <small>{{ $reviewCount }} ulasan</small>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @auth
        @if (auth()->user()->role === 'customer' && $canReview)
            <form action="{{ route('storefront.products.reviews.store', $product) }}" method="POST" class="review-form">
                @csrf
                <h3>{{ $userReview ? 'Perbarui ulasan kamu' : 'Bagaimana pengalamanmu dengan produk ini?' }}</h3>
                <p class="profile-section-help">Ulasan hanya dapat diberikan untuk pesanan yang sudah dibayar dan selesai.</p>
                <label for="rating">Rating produk</label>
                <select id="rating" name="rating" required>
                    <option value="">Pilih rating</option>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected((string) old('rating', $userReview?->rating) === (string) $rating)>
                            {{ $rating }} dari 5 bintang
                        </option>
                    @endfor
                </select>
                <label for="review">Ulasan kamu</label>
                <textarea id="review" name="review" rows="4" minlength="10" maxlength="2000" required placeholder="Ceritakan kualitas produk, pengalaman penggunaan, atau hal yang perlu diketahui pembeli lain...">{{ old('review', $userReview?->review) }}</textarea>
                <button type="submit" class="store-button">{{ $userReview ? 'Simpan Perubahan' : 'Kirim Ulasan' }}</button>
            </form>
        @elseif (auth()->user()->role === 'customer')
            <div class="review-notice">Kamu bisa menulis ulasan setelah pesanan produk ini berstatus selesai dan pembayaran terkonfirmasi.</div>
        @endif
    @else
        <div class="review-notice">
            Sudah membeli produk ini? <a href="{{ route('login') }}">Masuk ke akun</a> untuk menulis ulasan setelah pesanan selesai.
        </div>
    @endauth

    <div class="review-list">
        @forelse ($reviews as $review)
            <article class="review-item">
                <div class="review-item-top">
                    <strong>{{ $review->user->name }}</strong>
                    <span class="review-stars" aria-label="{{ $review->rating }} dari 5 bintang">
                        @for ($star = 1; $star <= 5; $star++)
                            {{ $star <= $review->rating ? '★' : '☆' }}
                        @endfor
                    </span>
                </div>
                <p>{{ $review->review }}</p>
                <small>{{ $review->created_at->format('d M Y') }} · Pembelian terverifikasi</small>
            </article>
        @empty
            <div class="review-empty">
                <strong>Belum ada ulasan</strong>
                <p>Jadilah customer pertama yang membagikan pengalaman menggunakan produk ini.</p>
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        <div class="review-pagination">{{ $reviews->fragment('reviews')->links() }}</div>
    @endif
</section>

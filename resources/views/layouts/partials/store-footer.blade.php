<footer class="arbyte-footer">
    <div class="arbyte-footer-inner">
        <div class="arbyte-footer-main">
            <div class="arbyte-footer-brand">
                <a href="{{ route('home') }}" class="arbyte-footer-logo" aria-label="Arbyte beranda">Arbyte<span>.</span></a>
                <p>Temukan perangkat teknologi untuk belajar, bekerja, berkarya, dan menikmati hiburan. Pilih produk dengan informasi yang jelas dan pengalaman belanja yang praktis.</p>
                <div class="arbyte-footer-socials">
                    <a href="https://www.instagram.com/grandmofftarkin?avpt=MWtwazk4azE0OXRzdQ==" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi Instagram Arbyte">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="6.7" r="1.1" fill="currentColor"/></svg>
                        <span>Instagram</span>
                    </a>
                    <a href="https://wa.me/628111188879" target="_blank" rel="noopener noreferrer" aria-label="Hubungi Arbyte melalui WhatsApp">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 11.8a8.1 8.1 0 0 1-12 7.1L4 20l1.1-4A8.1 8.1 0 1 1 20.2 11.8Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.6 8.3c.2-.5.4-.5.7-.5h.5c.2 0 .4.1.5.4l.7 1.6c.1.2.1.4-.1.6l-.5.6c-.2.2-.2.4 0 .6.5.9 1.3 1.6 2.2 2 .2.1.4.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.5.7c.3.1.4.3.4.5 0 .3-.2 1-.6 1.3-.4.4-1 .6-1.7.5-1-.1-2.2-.7-3.5-1.8-1.2-1-2-2.3-2.3-3.2-.3-.9 0-1.7.3-2.3Z" fill="currentColor"/></svg>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>

            <div class="arbyte-footer-column">
                <h2>Jelajahi</h2>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('storefront.products.index') }}">Semua Produk</a>
                @auth
                    @if (auth()->user()->role === 'customer')
                        <a href="{{ route('cart.index') }}">Keranjang Belanja</a>
                        <a href="{{ route('customer.orders.index') }}">Pesanan Saya</a>
                    @elseif (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">Admin Workspace</a>
                    @endif
                @else
                    <a href="{{ route('login') }}">Masuk ke Akun</a>
                    <a href="{{ route('register') }}">Buat Akun</a>
                @endauth
            </div>

            <div class="arbyte-footer-column">
                <h2>Kategori</h2>
                <span>Laptop &amp; Notebook</span>
                <span>Komponen PC</span>
                <span>Monitor &amp; Display</span>
                <span>Periferal</span>
                <span>Audio</span>
            </div>

            <div class="arbyte-footer-column arbyte-footer-contact">
                <h2>Butuh Bantuan?</h2>
                <p>Hubungi kami melalui kanal berikut untuk pertanyaan seputar produk dan pesanan.</p>
                <a href="https://wa.me/628111188879" target="_blank" rel="noopener noreferrer" class="arbyte-footer-contact-link">+62 811-1188-879</a>
                <a href="https://www.instagram.com/grandmofftarkin?avpt=MWtwazk4azE0OXRzdQ==" target="_blank" rel="noopener noreferrer">Instagram @grandmofftarkin</a>
                <span>Senin–Sabtu · 09.00–17.00 WIB</span>
            </div>
        </div>

        <div class="arbyte-footer-bottom">
            <span>© {{ date('Y') }} <strong>Arbyte</strong>. Seluruh hak cipta dilindungi.</span>
            <span>Teknologi untuk kebutuhan digital.</span>
        </div>
    </div>
</footer>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'TubesPEC - Online Store')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fb;
            color: #20242c;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .store-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-inner {
            max-width: 1180px;
            margin: auto;
            padding: 17px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .store-logo {
            font-size: 23px;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        .store-nav {
            display: flex;
            align-items: center;
            gap: 23px;
            font-size: 14px;
        }

        .store-nav a:hover {
            color: #64748b;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .store-container {
            max-width: 1180px;
            margin: auto;
            padding: 35px 22px 60px;
        }

        .hero {
            padding: 65px 45px;
            border-radius: 16px;
            background: #e9edf3;
            margin-bottom: 45px;
        }

        .hero h1 {
            max-width: 560px;
            font-size: clamp(30px, 5vw, 48px);
            line-height: 1.15;
            margin: 0 0 18px;
        }

        .hero p {
            max-width: 510px;
            color: #5c6573;
            line-height: 1.7;
        }

        .section-heading {
            margin: 38px 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: 23px;
        }

        .section-subtitle {
            color: #78808d;
            font-size: 14px;
            margin-top: 7px;
        }

        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-link {
            padding: 11px 17px;
            background: white;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .category-link:hover {
            border-color: #64748b;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            border: 1px solid #e8ebef;
            border-radius: 11px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(0,0,0,0.06);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            background: #edf0f4;
            display: block;
        }

        .image-placeholder {
            width: 100%;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf0f4;
            color: #89919e;
            font-size: 14px;
        }

        .product-info {
            padding: 15px;
        }

        .product-category {
            font-size: 12px;
            color: #7d8694;
        }

        .product-name {
            font-size: 15px;
            line-height: 1.4;
            margin: 9px 0;
            min-height: 42px;
        }

        .product-price {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .product-stock {
            font-size: 12px;
            color: #727b88;
        }

        .store-button {
            display: inline-block;
            padding: 12px 19px;
            border-radius: 7px;
            background: #252b36;
            color: white;
            font-size: 14px;
            border: 0;
            cursor: pointer;
        }

        .store-button:hover {
            background: #3c4655;
        }

        .button-light {
            background: white;
            color: #252b36;
            border: 1px solid #dce1e7;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .store-input,
        .store-select {
            padding: 12px 14px;
            border: 1px solid #dce1e7;
            border-radius: 7px;
            background: white;
            font-size: 14px;
        }

        .store-input {
            flex: 1;
            min-width: 100px;
        }

        .store-select {
            min-width: 175px;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            background: white;
            border: 1px solid #e8ebef;
            border-radius: 10px;
            color: #727b88;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            background: white;
            border: 1px solid #e8ebef;
            border-radius: 12px;
            padding: 28px;
        }

        .detail-image {
            width: 100%;
            border-radius: 9px;
            object-fit: cover;
            background: #edf0f4;
            aspect-ratio: 1 / 1;
        }

        .detail-price {
            font-size: 27px;
            font-weight: bold;
            margin: 22px 0;
        }

        .detail-description {
            color: #626c79;
            line-height: 1.8;
            white-space: pre-line;
        }

        .detail-meta {
            padding: 17px 0;
            border-top: 1px solid #e8ebef;
            border-bottom: 1px solid #e8ebef;
            margin: 20px 0;
            line-height: 1.9;
            font-size: 14px;
        }

        .spec-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .spec-table td {
            padding: 11px;
            border-bottom: 1px solid #e8ebef;
            font-size: 14px;
        }

        .spec-table td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .store-footer {
            padding: 25px 22px;
            background: white;
            border-top: 1px solid #e5e7eb;
            color: #7a8390;
            text-align: center;
            font-size: 13px;
        }

        .pagination-wrapper {
            margin-top: 30px;
        }

        @media (max-width: 900px) {
            .product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .header-inner {
                flex-wrap: wrap;
            }

            .store-nav {
                gap: 13px;
                font-size: 13px;
            }

            .product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .hero {
                padding: 40px 25px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                padding: 18px;
                gap: 25px;
            }

            .search-form {
                flex-direction: column;
            }

            .store-select {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header class="store-header">
    <div class="header-inner">

        <a href="{{ route('home') }}" class="store-logo">
            TubesPEC
        </a>

        <nav class="store-nav">
            <a href="{{ route('home') }}">Beranda</a>

            <a href="{{ route('storefront.products.index') }}">Produk</a>

            @auth
                @if (Auth::user()->role === 'customer')
                    <a href="{{ route('cart.index') }}">Keranjang</a>
                    <a href="{{ route('customer.orders.index') }}">Pesanan Saya</a>
                @endif
            @endauth
        </nav>

        <div class="header-user">
            @auth
                <a href="{{ route('dashboard') }}">
                    {{ Auth::user()->name }}
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button type="submit" class="store-button button-light">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>

                <a href="{{ route('register') }}" class="store-button">
                    Register
                </a>
            @endauth
        </div>

    </div>
</header>

<main class="store-container">

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif
    
    @if (session('error'))
        <div class="alert" style="background:#fee2e2; color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')

</main>

<footer class="store-footer">
    &copy; {{ date('Y') }} TubesPEC. All rights reserved.
</footer>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B1F3A">
    <title>@yield('title', 'TubesPEC - Online Store')</title>
    <link rel="stylesheet" href="{{ asset('css/tubespec.css') }}">
</head>
<body>
    @include('layouts.partials.customer-navigation')

    <main class="store-container">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="store-footer">
        © {{ date('Y') }} <strong>TubesPEC</strong>. Teknologi untuk kebutuhan digital.
    </footer>
</body>
</html>
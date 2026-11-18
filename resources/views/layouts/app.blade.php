<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TubesPEC')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            color: #20242c;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            color: #20242c;
            font-size: 21px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .nav-actions a {
            color: #343a46;
            text-decoration: none;
        }

        .container {
            width: 90%;
            max-width: 1050px;
            margin: 45px auto;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            margin: 45px auto;
            padding: 32px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        h1 {
            font-size: 26px;
            margin-top: 0;
        }

        .subtitle {
            color: #737b89;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d9e0;
            border-radius: 7px;
            font-size: 14px;
        }

        input:focus {
            outline: 2px solid #cbd5e1;
            border-color: #64748b;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
            font-size: 14px;
        }

        .checkbox-group input {
            width: auto;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            border: none;
            border-radius: 7px;
            background: #252b36;
            color: white;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .button-primary {
            width: 100%;
        }

        .button-logout {
            padding: 8px 13px;
        }

        .auth-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 14px;
        }

        .auth-footer a {
            color: #334155;
            font-weight: bold;
        }

        .alert {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .error-text {
            display: block;
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }

        .dashboard-card {
            background: white;
            padding: 28px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 15px 5%;
            }

            .nav-actions {
                gap: 10px;
            }

            .container {
                margin-top: 25px;
            }

            .auth-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="/" class="brand">TubesPEC</a>

        <div class="nav-actions">
            @auth
                <span>{{ Auth::user()->name }}</span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="button button-logout">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </nav>

    <main class="container">

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')

    </main>

</body>
</html>

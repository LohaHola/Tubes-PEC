@extends('layouts.app')

@section('title', 'Login - TubesPEC')

@section('content')
<div class="auth-card">

    <h1>Welcome Back</h1>

    <p class="subtitle">
        Login untuk melanjutkan ke akun TubesPEC.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login.process') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                autocomplete="email"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
            >
        </div>

        <label class="checkbox-group">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button type="submit" class="button button-primary">
            Login
        </button>
    </form>

    <div class="auth-footer">
        Belum memiliki akun?
        <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>

</div>
@endsection

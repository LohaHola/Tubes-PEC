@extends('layouts.app')

@section('title', 'Register - TubesPEC')

@section('content')
<div class="auth-card">

    <h1>Create Account</h1>

    <p class="subtitle">
        Buat akun customer untuk mulai menggunakan TubesPEC.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.process') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Nama lengkap"
                autocomplete="name"
                required
            >
        </div>

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
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                Konfirmasi Password
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Ulangi password"
                autocomplete="new-password"
                required
            >
        </div>

        <button type="submit" class="button button-primary">
            Register
        </button>
    </form>

    <div class="auth-footer">
        Sudah memiliki akun?
        <a href="{{ route('login') }}">Login</a>
    </div>

</div>
@endsection

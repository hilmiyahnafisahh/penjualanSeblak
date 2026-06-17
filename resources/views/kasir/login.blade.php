<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">
            <img src="{{ asset('logo_seblak.png') }}" alt="Seblak Sangkuriang">
            <div class="auth-brand-text">
                <div class="b-name">Seblak Sangkuriang</div>
                <div class="b-sub">Panel Kasir</div>
            </div>
        </div>

        <h1 class="auth-title">Masuk ke Panel Kasir</h1>
        <p class="auth-subtitle">Silakan login dengan akun kasir untuk Masuk</p>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('kasir.login') }}">
            @csrf
            <div class="form-field">
                <label for="k-email">Email</label>
                <input id="k-email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="kasir@seblak.id" required autofocus>
            </div>
            <div class="form-field">
                <label for="k-pass">Password</label>
                <input id="k-pass" type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-kasir-primary">Enter &rarr;</button>
        </form>

        <div class="auth-footer">
            Khusus akun kasir &nbsp;·&nbsp; Tekan <span class="kbd">Enter</span> untuk masuk
        </div>
    </div>
</div>

</body>
</html>

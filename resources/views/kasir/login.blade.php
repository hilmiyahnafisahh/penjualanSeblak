<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
</head>
<body>
<div class="container">
    <div class="card kasir-card p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:72px;height:72px;object-fit:contain;margin-bottom:1rem;background:#fff;border-radius:50%;padding:3px;">
            <h2 class="fw-bold">Seblak Sangkuriang</h2>
            <p class="text-muted">Login Panel Kasir</p>
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
            <div class="mb-3">
                <label class="form-label">Nama / Email</label>
                <input type="text" name="email" value="{{ old('email') }}" class="form-control" required autofocus placeholder="Contoh: Galang atau galang@seblak.com">
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

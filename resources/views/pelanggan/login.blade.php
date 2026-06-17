<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-pelanggan.css') }}">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('logo_seblak.png') }}" alt="Seblak Sangkuriang">
        </div>

        <h1 class="auth-title">Selamat <em>Datang</em></h1>
        <p class="auth-subtitle">Masuk untuk melanjutkan pesanan favorit Anda</p>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('pelanggan.login') }}">
            @csrf
            <div class="form-field">
                <label for="p-email">Alamat Email</label>
                <input id="p-email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required autofocus>
            </div>
            <div class="form-field">
                <label for="p-pass">Kata Sandi</label>
                <input id="p-pass" type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
            </div>
            <button type="submit" class="btn-pel-primary">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('pelanggan.register') }}">Daftar di sini</a>
        </div>
    </div>
</div>

</body>
</html>

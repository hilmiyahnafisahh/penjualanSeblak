<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-pelanggan.css') }}">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-card" style="max-width: 520px;">
        <div class="auth-logo">
            <img src="{{ asset('logo_seblak.png') }}" alt="Seblak Sangkuriang">
        </div>

        <h1 class="auth-title">Buat <em>Akun Baru</em></h1>
        <p class="auth-subtitle">Bergabunglah dan nikmati pengalaman seblak premium</p>

        @if($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pelanggan.register.submit') }}">
            @csrf

            <div class="form-field">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Sesuai identitas" required autofocus>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 0.875rem;">
                <div class="form-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required>
                </div>
                <div class="form-field">
                    <label>No. Telepon</label>
                    <input type="tel" name="no_telp" value="{{ old('no_telp') }}" class="form-control" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <div class="form-field">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control" required>
                    <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>-- Pilih jenis kelamin --</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin')==='Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin')==='Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="form-field">
                <label>Alamat Pengiriman</label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. ..." required style="resize:vertical;">{{ old('alamat') }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 0.875rem;">
                <div class="form-field">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                </div>
                <div class="form-field">
                    <label>Konfirmasi Sandi</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi sandi" required>
                </div>
            </div>

            <div style="height:0.5rem;"></div>
            <button type="submit" class="btn-pel-primary">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('pelanggan.login') }}">Masuk di sini</a>
        </div>
    </div>
</div>

</body>
</html>

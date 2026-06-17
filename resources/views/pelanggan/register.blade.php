<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar | Seblak Sangkuriang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
  <style>
    body { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 1.5rem; }
    .auth-page-wrap { width: 100%; max-width: 460px; margin: 1rem auto; }
    .auth-header-band { background: linear-gradient(135deg, var(--merah), #a32020); border-radius: 16px 16px 0 0; padding: 1.75rem 1.5rem 1.25rem; text-align: center; color: white; }
    .auth-body { background: white; border-radius: 0 0 16px 16px; padding: 1.75rem 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,.12); }
    .form-control, .form-select { border-radius: 8px; border: 1.5px solid #e0e0e0; padding: .55rem .85rem; font-size: .9rem; }
    .form-control:focus, .form-select:focus { border-color: var(--merah); box-shadow: 0 0 0 3px rgba(139,26,26,.1); }
    .form-label { font-size: .82rem; font-weight: 600; color: #444; margin-bottom: .35rem; }
    .row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    @media (max-width:480px) { .row-2col { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<div class="auth-page-wrap">
  <div class="auth-header-band">
    <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo" class="auth-logo mb-2">
    <div style="font-size:1.1rem; font-weight:800;">Seblak Sangkuriang</div>
    <div style="font-size:.78rem; opacity:.8; margin-top:.2rem;">Buat akun pelanggan baru</div>
  </div>

  <div class="auth-body">
    @if($errors->any())
      <div class="alert alert-danger rounded-3 py-2 small mb-3">
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('pelanggan.register.post') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus placeholder="Nama lengkap">
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required placeholder="email@contoh.com">
      </div>

      <div class="row-2col mb-3">
        <div>
          <label class="form-label">No. Telepon</label>
          <input type="tel" name="no_telp" value="{{ old('no_telp') }}" class="form-control" placeholder="08xx" required>
        </div>
        <div>
          <label class="form-label">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select" required>
            <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>-- Pilih --</option>
            <option value="Laki-laki"  {{ old('jenis_kelamin') === 'Laki-laki'  ? 'selected' : '' }}>Laki-laki</option>
            <option value="Perempuan"  {{ old('jenis_kelamin') === 'Perempuan'  ? 'selected' : '' }}>Perempuan</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. ..." required>{{ old('alamat') }}</textarea>
      </div>

      <div class="row-2col mb-4">
        <div>
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="Min. 8 karakter">
        </div>
        <div>
          <label class="form-label">Konfirmasi</label>
          <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi password">
        </div>
      </div>

      <button type="submit" class="btn btn-merah w-100 py-2 fw-bold rounded-3">
        Daftar Sekarang →
      </button>
    </form>

    <div class="text-center mt-4 small text-muted">
      Sudah punya akun?
      <a href="{{ route('pelanggan.login') }}" class="link-merah fw-bold">Login di sini</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Seblak Sangkuriang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
  <style>
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
    .auth-page-wrap { width: 100%; max-width: 420px; }
    .auth-header-band { background: linear-gradient(135deg, var(--merah), #a32020); border-radius: 16px 16px 0 0; padding: 2rem 1.5rem 1.5rem; text-align: center; color: white; }
    .auth-body { background: white; border-radius: 0 0 16px 16px; padding: 2rem 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,.12); }
    .form-control { border-radius: 8px; border: 1.5px solid #e0e0e0; padding: .55rem .85rem; font-size: .9rem; }
    .form-control:focus { border-color: var(--merah); box-shadow: 0 0 0 3px rgba(139,26,26,.1); }
    .form-label { font-size: .82rem; font-weight: 600; color: #444; margin-bottom: .35rem; }
  </style>
</head>
<body>
<div class="auth-page-wrap">
  <div class="auth-header-band">
    <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo" class="auth-logo mb-3">
    <div style="font-size:1.2rem; font-weight:800; letter-spacing:.2px;">Seblak Sangkuriang</div>
    <div style="font-size:.8rem; opacity:.8; margin-top:.25rem;">Masuk ke akun pelanggan</div>
  </div>

  <div class="auth-body">
    @if($errors->any())
      <div class="alert alert-danger rounded-3 py-2 small mb-3">
        <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('pelanggan.login.post') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus placeholder="email@contoh.com">
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-merah w-100 py-2 fw-bold rounded-3">
        Masuk →
      </button>
    </form>

    <div class="text-center mt-4 small text-muted">
      Belum punya akun?
      <a href="{{ route('pelanggan.register') }}" class="link-merah fw-bold">Daftar di sini</a>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

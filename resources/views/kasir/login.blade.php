<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        :root { --merah: #8b1a1a; --merah-light: #c85a57; }
        body { background: #fdf0f0; }
        .kasir-card { max-width: 420px; margin: 6rem auto; border-radius: 1.25rem; box-shadow: 0 20px 50px rgba(0,0,0,.08); }
        .btn-kasir { background: var(--merah); color: white; border-radius: .6rem; padding: .5rem; box-shadow: 0 8px 22px rgba(139,26,26,.08); border: none; }
        .btn-kasir:hover { background: var(--merah-light); }
    </style>
</head>
<body>
<div class="container">
    <div class="card kasir-card p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:72px;height:72px;object-fit:contain;margin-bottom:1rem;">
            <h2 class="fw-bold">Seblak Sangkuriang</h2>
            <p class="text-muted">Login Panel Kasir</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('kasir.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-kasir w-100 py-2">Masuk</button>
        </form>

        <div class="text-center mt-4 text-muted small">
            Gunakan akun kasir untuk akses khusus kasir.
        </div>
    </div>
</div>
</body>
</html>

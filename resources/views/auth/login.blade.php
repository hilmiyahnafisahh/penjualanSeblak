<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 { text-align: center; margin-bottom: 1.5rem; color: #1f2937; font-size: 1.5rem; }
        label { display: block; margin-bottom: 0.3rem; font-size: 0.875rem; color: #374151; }
        input {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        input:focus { outline: none; border-color: #6366f1; }
        .error { color: #dc2626; font-size: 0.8rem; margin-top: -0.8rem; margin-bottom: 0.8rem; }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { background: #4f46e5; }
        .register-link { text-align: center; margin-top: 1rem; font-size: 0.875rem; }
        .register-link a { color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🛒 Login Pelanggan</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pelanggan.login.post') }}">
            @csrf

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Masuk</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="{{ route('pelanggan.register') }}">Daftar di sini</a>
        </div>
    </div>
</body>
</html>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/css/pos.css">
    <style>
        .login-wrap{display:flex;min-height:100vh;align-items:center;justify-content:center;background:var(--muted)}
        .login-card{width:420px;padding:24px;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.06)}
        .login-card h3{margin-bottom:12px}
        .form-group{margin-bottom:10px}
        .input{width:100%}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h3>Admin Login - SeblakPOS</h3>
        @if($errors->any())
            <div style="color:#b00020;margin-bottom:8px">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="form-group">
                <input class="input" name="email" placeholder="Email" value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <input class="input" name="password" type="password" placeholder="Password">
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <button class="btn-primary">Login</button>
                <a href="/">Back</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

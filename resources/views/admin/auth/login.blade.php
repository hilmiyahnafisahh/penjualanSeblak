<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Seblak Sangkuriang</title>
    <link rel="icon" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --c-900: #3B0A0A;
            --c-800: #5C1010;
            --c-700: #7F1D1D;
            --c-600: #B91C1C;
            --c-500: #DC2626;
            --org:   #EA580C;
            --org-l: #FB923C;
            --white: #FFFFFF;
            --dark:  #111827;
            --mid:   #4B5563;
            --bdr:   #FECACA;
        }

        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background: #F7F0EF;
        }

        /* ── LAYOUT ─────────────────────────────── */
        .login-page {
            display: flex;
            min-height: 100vh;
        }

        /* Panel kiri — background gelap agar logo keliatan */
        .login-panel-left {
            display: none;
            flex: 1;
            background: linear-gradient(175deg,
                #1a0505 0%, #2d0808 25%, #3B0A0A 55%, #5C1010 80%, #7F1D1D 100%
            );
            position: relative;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
            padding: 3rem;
        }

        @media (min-width: 768px) {
            .login-panel-left { display: flex; }
        }

        /* Dekorasi lingkaran di panel kiri */
        .login-panel-left::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            top: -120px; right: -120px;
        }
        .login-panel-left::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            bottom: -80px; left: -80px;
        }

        .panel-logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            filter: drop-shadow(0 0 20px rgba(255,255,255,.25)) brightness(1.15);
            position: relative;
            z-index: 1;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            padding: 8px;
        }

        .panel-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 2rem;
            color: #fff;
            text-align: center;
            text-shadow: 0 3px 12px rgba(0,0,0,.5);
            letter-spacing: .02em;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }

        .panel-tagline {
            font-size: .875rem;
            color: rgba(255,255,255,.6);
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .panel-divider {
            width: 48px;
            height: 3px;
            border-radius: 9999px;
            background: rgba(255,255,255,.35);
            position: relative;
            z-index: 1;
        }

        /* Panel kanan — form login */
        .login-panel-right {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 2rem 1.5rem;
            background: #F7F0EF;
        }

        /* Card */
        .login-card {
            width: 100%;
            max-width: 400px;
        }

        /* Header card (mobile: tampilkan logo+brand) */
        .card-header-mobile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .card-header-mobile { display: none; }
        }

        .card-header-mobile img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(127,29,29,.4));
        }

        .card-header-mobile .brand-name {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 1.25rem;
            color: var(--c-700);
            text-align: center;
        }

        /* Box form */
        .form-box {
            background: #fff;
            border-radius: 1.25rem;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 4px 24px rgba(127,29,29,.10);
            border: 1px solid var(--bdr);
            overflow: hidden;
            position: relative;
        }

        .form-box::before {
            content: '';
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--c-700), var(--c-500), var(--org));
            position: absolute;
            top: 0; left: 0; right: 0;
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--c-700);
            margin-bottom: .25rem;
            margin-top: .75rem;
        }

        .form-subtitle {
            font-size: .8rem;
            color: var(--mid);
            margin-bottom: 1.75rem;
        }

        /* Error */
        .error-box {
            background: #FEF2F2;
            border: 1px solid var(--bdr);
            border-left: 3px solid var(--c-500);
            color: var(--c-700);
            font-size: .8rem;
            padding: .65rem .875rem;
            border-radius: .5rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        /* Form group */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--c-700);
            margin-bottom: .4rem;
        }

        .form-input {
            width: 100%;
            padding: .65rem .875rem;
            background: #FAFAFA;
            border: 1.5px solid var(--bdr);
            border-radius: .65rem;
            font-family: 'Poppins', sans-serif;
            font-size: .875rem;
            color: var(--dark);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input:focus {
            border-color: var(--org);
            box-shadow: 0 0 0 3px rgba(234,88,12,.1);
            background: #fff;
        }

        .form-input::placeholder {
            color: #9CA3AF;
        }

        /* Tombol login */
        .btn-login {
            width: 100%;
            padding: .75rem 1rem;
            background: linear-gradient(135deg, var(--c-600) 0%, var(--org) 100%);
            color: #fff;
            border: none;
            border-radius: .75rem;
            font-family: 'Poppins', sans-serif;
            font-size: .9rem;
            font-weight: 700;
            letter-spacing: .02em;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(220,38,38,.32);
            transition: all .2s;
            margin-top: .5rem;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--c-700) 0%, var(--c-600) 100%);
            box-shadow: 0 6px 22px rgba(220,38,38,.4);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer card */
        .form-footer {
            display: flex;
            justify-content: center;
            margin-top: 1.25rem;
        }

        .form-footer a {
            font-size: .78rem;
            color: var(--mid);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .375rem;
            transition: color .2s;
        }

        .form-footer a:hover {
            color: var(--org);
        }

        .form-footer a svg {
            width: .875rem;
            height: .875rem;
        }
    </style>
</head>
<body>
<div class="login-page">

    {{-- Panel kiri: dekorasi --}}
    <div class="login-panel-left">
        <img src="/images/logo.png" alt="Seblak Sangkuriang" class="panel-logo">
        <div class="panel-divider"></div>
        <div class="panel-brand">SEBLAK<br>SANGKURIANG</div>
        <p class="panel-tagline">Sistem Manajemen Penjualan</p>
    </div>

    {{-- Panel kanan: form --}}
    <div class="login-panel-right">
        <div class="login-card">

            {{-- Logo + brand hanya di mobile --}}
            <div class="card-header-mobile">
                <img src="/images/logo.png" alt="Logo">
                <span class="brand-name">SEBLAK SANGKURIANG</span>
            </div>

            <div class="form-box">
                <h2 class="form-title">Selamat Datang</h2>
                <p class="form-subtitle">Masuk ke panel admin Seblak Sangkuriang</p>

                @if($errors->any())
                    <div class="error-box">{{ $errors->first() }}</div>
                @endif

                <form method="post" action="{{ route('admin.login.post') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            class="form-input"
                            type="email"
                            name="email"
                            placeholder="admin@example.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input
                            id="password"
                            class="form-input"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        >
                    </div>
                    <button type="submit" class="btn-login">Masuk</button>
                </form>

                <div class="form-footer">
                </div>
            </div>

        </div>
    </div>

</div>
</body>
</html>

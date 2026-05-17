<!DOCTYPE html>
<html lang="id">
  <head>
    <title>@yield('title', 'Seblak Nusantara')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{asset('css/vendor.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('style.css')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Open+Sans:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{env('MIDTRANS_CLIENT_KEY')}}"></script>

    <style>
      :root {
        --seblak-red:    #c0392b;
        --seblak-orange: #e67e22;
        --seblak-dark:   #1a1a1a;
      }

      body { font-family: 'Nunito', sans-serif; background: #f8f9fa; }

      /* ── NAVBAR ── */
      .navbar-seblak {
        background: linear-gradient(135deg, var(--seblak-red) 0%, var(--seblak-orange) 100%);
        box-shadow: 0 2px 12px rgba(0,0,0,.18);
        padding: .75rem 1.5rem;
      }
      .navbar-seblak .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff !important;
        letter-spacing: .5px;
      }
      .navbar-seblak .navbar-brand span { color: #ffe082; }
      .navbar-seblak .nav-link {
        color: rgba(255,255,255,.9) !important;
        font-weight: 600;
        transition: color .2s;
      }
      .navbar-seblak .nav-link:hover { color: #ffe082 !important; }
      .navbar-seblak .navbar-toggler { border-color: rgba(255,255,255,.5); }
      .navbar-seblak .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255,255,255,0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }

      /* cart badge */
      .cart-badge {
        background: #ffe082;
        color: var(--seblak-dark);
        font-weight: 700;
        font-size: .7rem;
        border-radius: 50%;
        padding: 2px 6px;
        margin-left: 4px;
      }

      /* ── OFFCANVAS CART ── */
      .offcanvas-cart-header {
        background: linear-gradient(135deg, var(--seblak-red), var(--seblak-orange));
        color: #fff;
        padding: 1rem 1.5rem;
      }
      .offcanvas-cart-header h5 { margin: 0; font-weight: 700; }

      /* ── PRODUCT CARD ── */
      .product-item {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        transition: transform .2s, box-shadow .2s;
        background: #fff;
        margin-bottom: 1.5rem;
      }
      .product-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.14);
      }
      .product-item figure { margin: 0; overflow: hidden; height: 200px; }
      .product-item figure img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
      .product-item:hover figure img { transform: scale(1.05); }
      .product-item h3 { font-size: 1rem; font-weight: 700; padding: .75rem 1rem .25rem; margin: 0; }
      .product-item .qty, .product-item .price, .product-item .rating {
        font-size: .85rem;
        padding: 0 1rem;
        display: block;
        color: #666;
      }
      .product-item .price { color: var(--seblak-red); font-weight: 700; font-size: 1rem; margin: .25rem 0; }
      .product-item .d-flex { padding: .75rem 1rem 1rem; }

      /* ── PAGE HEADER ── */
      .page-hero {
        background: linear-gradient(135deg, var(--seblak-red) 0%, var(--seblak-orange) 100%);
        color: #fff;
        padding: 2.5rem 0 2rem;
        margin-bottom: 2rem;
      }
      .page-hero h1 { font-weight: 700; font-size: 1.8rem; margin: 0; }
      .page-hero p { margin: .5rem 0 0; opacity: .85; }

      /* ── SECTION TITLE ── */
      .section-title {
        font-weight: 700;
        font-size: 1.4rem;
        color: var(--seblak-dark);
        border-left: 4px solid var(--seblak-red);
        padding-left: .75rem;
        margin-bottom: 1.5rem;
      }

      /* ── FOOTER ── */
      footer { background: #1a1a1a; color: #ccc; }
      footer .widget-title { color: #fff; font-weight: 700; margin-bottom: 1rem; }
      footer .nav-link { color: #aaa; padding: .2rem 0; }
      footer .nav-link:hover { color: #ffe082; }
      #footer-bottom { background: #111; color: #888; font-size: .85rem; padding: 1rem 0; }
      #footer-bottom a { color: #ffe082; text-decoration: none; }

      /* ── RIWAYAT ── */
      .riwayat-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        background: #fff;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
      }
      .riwayat-card .card-header-custom {
        background: linear-gradient(90deg, var(--seblak-red), var(--seblak-orange));
        color: #fff;
        padding: .75rem 1.25rem;
        font-weight: 700;
      }
      .riwayat-card .card-body-custom { padding: 1rem 1.25rem; }
      .badge-status-selesai  { background: #28a745; color: #fff; }
      .badge-status-diproses { background: #fd7e14; color: #fff; }
      .badge-status-pending  { background: #ffc107; color: #000; }
      .badge-status-batal    { background: #dc3545; color: #fff; }

      /* ── KERANJANG ── */
      .keranjang-item {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        background: #fff;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
        display: flex;
        gap: 1rem;
        align-items: center;
      }
      .keranjang-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
      .keranjang-total-box {
        background: linear-gradient(135deg, var(--seblak-red), var(--seblak-orange));
        color: #fff;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-top: 1rem;
      }
      .keranjang-total-box .label { font-size: .9rem; opacity: .85; }
      .keranjang-total-box .amount { font-size: 1.6rem; font-weight: 700; }

      /* ── PRELOADER ── */
      .preloader-wrapper { display: none; }
    </style>
  </head>
  <body>

    {{-- SVG Sprite --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none;">
      <defs>
        <symbol id="cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
        <symbol id="heart" viewBox="0 0 24 24"><path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/></symbol>
        <symbol id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
        <symbol id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
        <symbol id="trash" viewBox="0 0 24 24"><path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1ZM20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2ZM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1Z"/></symbol>
        <symbol id="star-solid" viewBox="0 0 15 15"><path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/></symbol>
        <symbol id="user" viewBox="0 0 24 24"><path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/></symbol>
        <symbol id="history" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8a8 8 0 0 1-8 8Zm1-8.59V7a1 1 0 0 0-2 0v5a1 1 0 0 0 .29.71l3 3a1 1 0 0 0 1.42-1.42Z"/></symbol>
      </defs>
    </svg>

    {{-- Offcanvas Cart --}}
    <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart">
      <div class="offcanvas-cart-header d-flex justify-content-between align-items-center">
        <h5>🛒 Keranjang Anda</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column gap-3 pt-3">
        <div class="d-flex justify-content-between align-items-center px-1">
          <span class="text-muted">Jumlah Item</span>
          <span id="cart-count" class="badge rounded-pill bg-danger fs-6">{{ $jmlbarangdibeli ?? $jml_brg ?? 0 }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center px-1 fw-bold fs-5">
          <span>Total</span>
          <span id="cart-total">{{ rupiah($total_belanja ?? $total_tagihan ?? 0) }}</span>
        </div>
        <hr class="my-1">
        <a href="/lihatkeranjang" class="btn btn-danger w-100">🛒 Lihat Keranjang</a>
        <a href="/depan" class="btn btn-outline-secondary w-100">🍜 Lihat Menu</a>
        <a href="/lihatriwayat" class="btn btn-outline-info w-100">📋 Riwayat Pesanan</a>
        <a href="/logout" class="btn btn-outline-danger w-100 mt-auto">Keluar</a>
      </div>
    </div>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-seblak sticky-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="/depan">
          🌶️ Seblak <span>Nusantara</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('depan') ? 'fw-bold' : '' }}" href="/depan">Menu</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('lihatriwayat') ? 'fw-bold' : '' }}" href="/lihatriwayat">Riwayat</a>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto align-items-center gap-2">
            <li class="nav-item">
              <a href="{{ url('/ubahpassword') }}" class="nav-link" title="Profil">
                <svg width="20" height="20"><use xlink:href="#user"></use></svg>
              </a>
            </li>
            <li class="nav-item">
              <button class="btn btn-sm btn-light d-flex align-items-center gap-1 fw-bold"
                      data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
                <svg width="20" height="20"><use xlink:href="#cart"></use></svg>
                <span id="total_belanja">{{ rupiah($total_belanja ?? $total_tagihan ?? 0) }}</span>
                <span class="cart-badge" id="cart-count-nav">{{ $jmlbarangdibeli ?? $jml_brg ?? 0 }}</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </nav>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Seblak Sangkuriang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --merah:#8b1a1a; --merah-gelap:#600e0e; --bg:#fdf0f0; }
        * { box-sizing: border-box; }
        body { background:var(--bg); font-family:'Segoe UI',sans-serif; margin:0; }

        /* ── NAVBAR ── */
        .navbar-custom {
            background:white; position:sticky; top:0; z-index:200;
            box-shadow:0 2px 12px rgba(0,0,0,.08);
        }
        .brand-logo { height:44px; width:44px; border-radius:50%; object-fit:contain; }
        .brand-name { font-weight:700; color:var(--merah); font-size:1rem; line-height:1; }
        .brand-sub  { font-size:.65rem; color:#aaa; }
        .search-bar { border-radius:2rem; border:1.5px solid #ddd; padding:.45rem 1rem .45rem 2.5rem; font-size:.875rem; width:100%; }
        .search-bar:focus { border-color:var(--merah); box-shadow:0 0 0 3px rgba(139,26,26,.1); outline:none; }
        .search-wrap { position:relative; }
        .search-icon { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:#aaa; pointer-events:none; }
        .cart-btn { position:relative; background:#f5f5f5; border:none; border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; }
        .cart-badge { position:absolute; top:-4px; right:-4px; background:var(--merah); color:white; border-radius:50%; font-size:.6rem; width:17px; height:17px; display:flex; align-items:center; justify-content:center; font-weight:700; }
        .btn-merah { background:var(--merah); color:white; border:none; }
        .btn-merah:hover { background:var(--merah-gelap); color:white; }
        .nav-tabs-custom { border-bottom:2px solid #f0d0d0; }
        .nav-tabs-custom .nav-link { color:#555; border:none; padding:.6rem 1.2rem; font-size:.875rem; border-radius:0; }
        .nav-tabs-custom .nav-link.active { color:var(--merah); font-weight:700; border-bottom:2px solid var(--merah); margin-bottom:-2px; }
        .nav-tabs-custom .nav-link:hover { color:var(--merah); background:none; }

        /* ── HERO BANNER ── */
        .hero-banner {
            background: linear-gradient(135deg, var(--merah) 0%, #c0392b 60%, #e74c3c 100%);
            color:white; padding:2.5rem 2rem; border-radius:1.5rem; margin:1.5rem 1rem;
            position:relative; overflow:hidden;
        }
        .hero-banner::after {
            content:'🍲'; position:absolute; right:1.5rem; top:50%; transform:translateY(-50%);
            font-size:5rem; opacity:.25;
        }
        .hero-banner h2 { font-size:1.6rem; font-weight:800; margin-bottom:.4rem; }
        .hero-banner p  { opacity:.85; margin-bottom:1rem; font-size:.9rem; }
        .hero-badge { background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.4); color:white; border-radius:2rem; padding:.25rem .9rem; font-size:.8rem; display:inline-block; margin-bottom:.75rem; }

        /* ── KATEGORI PILLS ── */
        .kategori-scroll { display:flex; gap:.5rem; overflow-x:auto; padding:.75rem 1rem; scrollbar-width:none; }
        .kategori-scroll::-webkit-scrollbar { display:none; }
        .kategori-pill {
            background:white; border:1.5px solid #e0e0e0; border-radius:2rem;
            padding:.35rem 1.1rem; font-size:.82rem; white-space:nowrap; cursor:pointer;
            text-decoration:none; color:#555; transition:all .15s; flex-shrink:0;
        }
        .kategori-pill.active, .kategori-pill:hover { background:var(--merah); color:white; border-color:var(--merah); }

        /* ── PRODUCT CARD ── */
        .product-card {
            background:white; border-radius:1.1rem; overflow:hidden; border:none;
            box-shadow:0 2px 12px rgba(0,0,0,.06); transition:transform .2s, box-shadow .2s;
            height:100%;
        }
        .product-card:hover { transform:translateY(-4px); box-shadow:0 10px 28px rgba(0,0,0,.12); }
        .product-img-wrap {
            position:relative; aspect-ratio:1/1; overflow:hidden; background:#fdf0f0;
        }
        .product-img-wrap img { width:100%; height:100%; object-fit:cover; }
        .product-img-emoji { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:3.5rem; }
        .product-body { padding:.9rem; }
        .product-name { font-weight:700; font-size:.9rem; color:#1a1a1a; margin-bottom:.15rem; }
        .product-kategori { font-size:.72rem; color:#aaa; margin-bottom:.3rem; }
        .product-price { font-weight:800; font-size:1rem; color:var(--merah); margin-bottom:.75rem; }
        .btn-order {
            background:var(--merah); color:white; border:none; border-radius:.6rem;
            width:100%; padding:.45rem; font-size:.8rem; font-weight:600; cursor:pointer;
            transition:background .15s;
        }
        .btn-order:hover { background:var(--merah-gelap); }
        .btn-order-outline {
            background:white; color:var(--merah); border:1.5px solid var(--merah);
            border-radius:.6rem; width:100%; padding:.45rem; font-size:.8rem; font-weight:600;
            cursor:pointer; transition:all .15s;
        }
        .btn-order-outline:hover { background:var(--merah); color:white; }

        /* ── SECTION ── */
        .section-header { padding:0 1rem .3rem; }
        .section-title { font-weight:800; font-size:1.1rem; color:#1a1a1a; margin-bottom:.1rem; }
        .section-sub { font-size:.78rem; color:#aaa; }

        /* ── TOAST ── */
        .toast-container { position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; }

        /* ── POPUP (SweetAlert-style) ── */
        .popup-overlay {
            display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
            z-index:5000; align-items:center; justify-content:center;
        }
        .popup-overlay.show { display:flex; }
        .popup-box {
            background:white; border-radius:1.25rem; padding:2rem; max-width:340px;
            width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.2);
            animation:popIn .25s ease;
        }
        @keyframes popIn { from{transform:scale(.85);opacity:0} to{transform:scale(1);opacity:1} }
        .popup-icon { font-size:3rem; margin-bottom:1rem; }
        .popup-title { font-weight:700; font-size:1.1rem; margin-bottom:.5rem; }
        .popup-msg { color:#666; font-size:.875rem; margin-bottom:1.5rem; }
    </style>
</head>
<body>

{{-- ── SVG DEFS ── --}}
<svg style="display:none;">
  <defs>
    <symbol id="ic-user" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/></symbol>
    <symbol id="ic-cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
    <symbol id="ic-search" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7 7 7 0 0 1-7 7Z"/></symbol>
  </defs>
</svg>

{{-- ── POPUP SUKSES ── --}}
<div class="popup-overlay" id="popupSukses">
  <div class="popup-box">
    <div class="popup-icon">🛒</div>
    <div class="popup-title" id="popupTitle">Berhasil ditambahkan!</div>
    <div class="popup-msg" id="popupMsg">Item telah masuk ke keranjang Anda.</div>
    <div class="d-grid gap-2">
      <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-merah">Lihat Keranjang</a>
      <button class="btn btn-outline-secondary" onclick="tutupPopup()">Lanjut Belanja</button>
    </div>
  </div>
</div>

{{-- ── OFFCANVAS MENU ── --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <img src="{{ asset('images/logo-seblak.png') }}" style="height:36px;width:36px;border-radius:50%;object-fit:contain;">
      <div>
        <div style="font-weight:700;font-size:.9rem;color:var(--merah);">Seblak Sangkuriang</div>
        <div style="font-size:.72rem;color:#aaa;">{{ session('pelanggan_user_name','Pelanggan') }}</div>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="list-group list-group-flush">
      <a href="{{ route('pelanggan.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-4">
        <i class="bi bi-house-door" style="color:var(--merah)"></i> Dashboard
      </a>
      <a href="{{ route('pelanggan.keranjang') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-4">
        <i class="bi bi-cart3" style="color:var(--merah)"></i> Keranjang
        @php $totalK = collect($keranjang??[])->sum('qty') @endphp
        @if($totalK>0)<span class="badge ms-auto" style="background:var(--merah)">{{ $totalK }}</span>@endif
      </a>
      <a href="{{ route('pelanggan.pesanan') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-4">
        <i class="bi bi-bag-check" style="color:var(--merah)"></i> Pesanan Saya
      </a>
      <a href="{{ route('pelanggan.riwayat') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-4">
        <i class="bi bi-clock-history" style="color:var(--merah)"></i> Riwayat Pemesanan
      </a>
    </nav>
    <div class="p-4 border-top mt-auto">
      <form action="{{ route('pelanggan.logout') }}" method="POST">
        @csrf
        <button class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
      </form>
    </div>
  </div>
</div>

{{-- ── NAVBAR ── --}}
<nav class="navbar-custom">
  <div class="container-fluid px-3 px-lg-4">
    <div class="d-flex align-items-center py-2 gap-3">
      {{-- Brand --}}
      <div class="d-flex align-items-center gap-2 flex-shrink-0">
        <img src="{{ asset('images/logo-seblak.png') }}" class="brand-logo" alt="Logo">
        <div class="d-none d-sm-block">
          <div class="brand-name">Seblak Sangkuriang</div>
          <div class="brand-sub">Pesan Sekarang</div>
        </div>
      </div>
      {{-- Search --}}
      <form action="{{ route('pelanggan.dashboard') }}" method="GET" class="search-wrap flex-grow-1 d-none d-md-block">
        <svg class="search-icon" width="15" height="15"><use xlink:href="#ic-search"></use></svg>
        <input type="text" name="q" value="{{ request('q') }}" class="search-bar" placeholder="Cari menu seblak...">
      </form>
      {{-- Actions --}}
      <div class="d-flex align-items-center gap-2 ms-auto">
        <a href="{{ route('pelanggan.keranjang') }}" class="cart-btn text-decoration-none" style="color:#333;">
          <svg width="20" height="20"><use xlink:href="#ic-cart"></use></svg>
          @if(($totalK??0)>0)<span class="cart-badge">{{ $totalK }}</span>@endif
        </a>
        <div class="d-none d-md-block text-end" style="line-height:1.1;">
          <div style="font-size:.65rem;color:#aaa;">Keranjang</div>
          <div style="font-size:.82rem;font-weight:700;">Rp {{ number_format(collect($keranjang??[])->sum('subtotal'),0,',','.') }}</div>
        </div>
        <span class="d-none d-lg-block text-muted" style="font-size:.8rem;">{{ session('pelanggan_user_name','') }}</span>
        <button class="btn btn-light rounded-circle p-1" style="width:38px;height:38px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="18" height="18"><use xlink:href="#ic-user"></use></svg>
        </button>
      </div>
    </div>
    {{-- Search mobile --}}
    <div class="d-md-none pb-2">
      <form action="{{ route('pelanggan.dashboard') }}" method="GET" class="search-wrap">
        <svg class="search-icon" width="15" height="15"><use xlink:href="#ic-search"></use></svg>
        <input type="text" name="q" value="{{ request('q') }}" class="search-bar" placeholder="Cari menu seblak...">
      </form>
    </div>
    {{-- Nav tabs --}}
    <ul class="nav nav-tabs-custom">
      <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link active">Beranda</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link">Pesanan Saya</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link">Riwayat</a></li>
    </ul>
  </div>
</nav>

{{-- ── TOAST NOTIFICATION ── --}}
@if(session('success'))
<div class="toast-container">
  <div class="toast show align-items-center text-white border-0" style="background:var(--merah);" role="alert">
    <div class="d-flex">
      <div class="toast-body"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endif

{{-- ── HERO BANNER ── --}}
@if(empty(request('q')))
<div class="hero-banner">
  <div class="hero-badge">🔥 Menu Spesial Hari Ini</div>
  <h2>Seblak Sangkuriang<br>Siap Melayani!</h2>
  <p>Pilih menu favoritmu dan nikmati kelezatan seblak autentik.</p>
  <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-light fw-bold" style="border-radius:2rem;color:var(--merah);">
    <i class="bi bi-cart3 me-1"></i> Lihat Keranjang
  </a>
</div>
@else
<div class="px-3 pt-3">
  <div class="alert" style="background:#fff3cd;border:none;border-radius:1rem;color:#856404;">
    <i class="bi bi-search me-2"></i>Hasil pencarian untuk: <strong>"{{ request('q') }}"</strong>
    <a href="{{ route('pelanggan.dashboard') }}" class="float-end text-decoration-none" style="color:#856404;">✕ Reset</a>
  </div>
</div>
@endif

{{-- ── KATEGORI PILLS ── --}}
<div class="kategori-scroll">
  @php $kategoriList = isset($kategoriList) ? array_merge(['Semua'], $kategoriList) : ['Semua']; $kategoriAktif = $kategoriParam ?? 'Semua'; @endphp
  @foreach($kategoriList as $kat)
    <a href="{{ route('pelanggan.dashboard', ['kategori'=>$kat]) }}"
       class="kategori-pill {{ $kategoriAktif===$kat?'active':'' }}">{{ $kat }}</a>
  @endforeach
</div>

{{-- ── PRODUCT GRID ── --}}
<div class="px-3 pb-5">
  <div class="section-header mb-3">
    <div class="section-title">
      @if(!empty(request('q'))) Hasil Pencarian
      @elseif($kategoriAktif !== 'Semua') {{ $kategoriAktif }}
      @else Menu Pilihan
      @endif
    </div>
    <div class="section-sub">{{ isset($produk) ? $produk->count() : 0 }} menu tersedia</div>
  </div>

  @if(isset($produk) && $produk->isNotEmpty())
    <div class="row g-3">
      @foreach($produk as $item)
        <div class="col-6 col-md-4 col-lg-3">
          <div class="product-card">
            <div class="product-img-wrap">
              @if($item->gambar_menu)
                <img src="{{ asset('storage/'.$item->gambar_menu) }}" alt="{{ $item->nama_menu }}" loading="lazy">
              @else
                <div class="product-img-emoji">🍲</div>
              @endif
            </div>
            <div class="product-body">
              <div class="product-kategori">{{ $item->kategori_menu }}</div>
              <div class="product-name">{{ $item->nama_menu }}</div>
              <div class="product-price">Rp {{ number_format($item->harga_menu, 0, ',', '.') }}</div>
              @if(strtolower($item->kategori_menu) === 'makanan')
                <a href="{{ route('pelanggan.menu.show', $item->id_menu) }}" class="btn-order d-block text-center text-decoration-none">
                  Pilih & Pesan
                </a>
              @else
                <form action="{{ route('pelanggan.keranjang.tambah') }}" method="POST" class="form-addcart">
                  @csrf
                  <input type="hidden" name="id_produk" value="{{ $item->id_menu }}">
                  <input type="hidden" name="qty" value="1">
                  <button type="submit" class="btn-order w-100">+ Keranjang</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="text-center py-5">
      <div style="font-size:3rem;">🔍</div>
      <p class="text-muted mt-2">Tidak ada menu ditemukan.</p>
      @if(!empty(request('q')))
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah btn-sm">Lihat Semua Menu</a>
      @endif
    </div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Popup setelah add to cart
function tutupPopup() { document.getElementById('popupSukses').classList.remove('show'); }

document.querySelectorAll('.form-addcart').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    const nama = form.closest('.product-card').querySelector('.product-name').textContent;
    btn.textContent = '✓ Ditambahkan';
    btn.disabled = true;

    fetch(form.action, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(new FormData(form))
    }).then(r => {
      if (r.ok || r.redirected) {
        document.getElementById('popupTitle').textContent = nama + ' ditambahkan!';
        document.getElementById('popupMsg').textContent = 'Item berhasil masuk ke keranjang Anda.';
        document.getElementById('popupSukses').classList.add('show');
        // Update cart count (reload soft)
        setTimeout(() => { btn.textContent = '+ Keranjang'; btn.disabled = false; }, 2000);
      }
    }).catch(() => { form.submit(); });
  });
});

// Auto-dismiss toast
setTimeout(() => { document.querySelectorAll('.toast.show').forEach(t => t.classList.remove('show')); }, 4000);
</script>
</body>
</html>

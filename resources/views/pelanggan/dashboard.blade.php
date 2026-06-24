<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-pelanggan.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
    <style>
        /* page-specific overrides only */
        .level-pedas-wrap { display: flex; gap: .4rem; flex-wrap: wrap; }
        .level-btn { border: 1.5px solid #ddd; border-radius: .5rem; padding: .3rem .75rem; font-size: .78rem; cursor: pointer; transition: all .15s; background: white; }
        .level-btn.active { background: var(--merah); color: white; border-color: var(--merah); }

        /* Pastikan product-card mengisi cell grid dengan baik */
        .p-grid .product-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .p-grid .product-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .p-grid .btn-order,
        .p-grid .form-addcart { margin-top: auto; }
        .p-grid .form-addcart .btn-order { margin-top: 0; }
    </style>
</head>
<body>

@php $totalK = collect($keranjang ?? [])->sum('qty'); @endphp

<!-- ============ NAVBAR ============ -->
<nav class="p-navbar">
  <div class="p-nav-inner">
    <a href="{{ route('pelanggan.dashboard') }}" class="p-brand">
      <img src="{{ asset('logo_seblak.png') }}" alt="Seblak Sangkuriang">
      <div>
        <div class="p-brand-name">Seblak Sangkuriang</div>
        <div class="p-brand-sub">Pedas & Lezat</div>
      </div>
    </a>

    <form action="{{ route('pelanggan.dashboard') }}" method="GET" class="p-search-wrap">
      <i class="bi bi-search search-icon"></i>
      <input type="text" name="q" value="{{ request('q') }}" class="p-search" placeholder="Cari menu seblak…">
    </form>

    <div class="p-nav-actions">
      <a href="{{ route('pelanggan.keranjang') }}" class="p-cart-btn" title="Keranjang">
        <i class="bi bi-bag"></i>
        @if($totalK > 0)<span class="p-cart-badge">{{ $totalK }}</span>@endif
      </a>
      <button class="p-user-btn" onclick="document.getElementById('pSide').classList.add('show'); document.getElementById('pSideBd').classList.add('show');" title="Akun">
        {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
      </button>
    </div>
  </div>

  <div class="p-nav-tabs">
    <a href="{{ route('pelanggan.dashboard') }}" class="p-nav-tab active">Beranda</a>
    <a href="{{ route('pelanggan.pesanan') }}" class="p-nav-tab">Pesanan Saya</a>
    <a href="{{ route('pelanggan.riwayat') }}" class="p-nav-tab">Riwayat</a>
  </div>
</nav>

<!-- ============ SIDE PANEL ============ -->
<div class="p-side-backdrop" id="pSideBd" onclick="document.getElementById('pSide').classList.remove('show'); this.classList.remove('show');"></div>
<aside class="p-side" id="pSide">
  <div class="p-side-head">
    <img src="{{ asset('logo_seblak.png') }}" style="width:36px;height:36px;border-radius:50%;object-fit:contain;">
    <div>
      <div style="font-family:var(--font-display);font-weight:700;color:var(--ink-900);font-size:0.95rem;">{{ Auth::user()->name ?? 'Tamu' }}</div>
      <div style="font-size:0.72rem;color:var(--gold-600);text-transform:uppercase;letter-spacing:0.1em;font-weight:600;">Pelanggan</div>
    </div>
    <button class="close" onclick="document.getElementById('pSide').classList.remove('show'); document.getElementById('pSideBd').classList.remove('show');">&times;</button>
  </div>
  <div class="p-side-body">
    <a href="{{ route('pelanggan.dashboard') }}" class="p-side-link"><i class="bi bi-house-door"></i> Beranda</a>
    <a href="{{ route('pelanggan.keranjang') }}" class="p-side-link">
      <i class="bi bi-bag"></i> Keranjang
      @if($totalK > 0)<span class="badge">{{ $totalK }}</span>@endif
    </a>
    <a href="{{ route('pelanggan.pesanan') }}" class="p-side-link"><i class="bi bi-bag-check"></i> Pesanan Saya</a>
    <a href="{{ route('pelanggan.riwayat') }}" class="p-side-link"><i class="bi bi-clock-history"></i> Riwayat Pemesanan</a>
  </div>
  <div class="p-side-foot">
    <form action="{{ route('pelanggan.logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn-side-logout"><i class="bi bi-box-arrow-right"></i> Keluar</button>
    </form>
  </div>
</aside>

<!-- ============ TOAST ============ -->
@if(session('success'))
<div class="p-toast" id="pToast"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
@endif

<!-- ============ POPUP ============ -->
<div class="p-popup-overlay" id="popupSukses">
  <div class="p-popup-box">
    <div class="p-popup-icon">🛍️</div>
    <div class="p-popup-title" id="popupTitle">Berhasil ditambahkan</div>
    <div class="p-popup-msg" id="popupMsg">Item telah masuk ke keranjang Anda.</div>
    <div class="p-popup-actions">
      <a href="{{ route('pelanggan.keranjang') }}" class="btn-pop-primary" style="text-decoration:none; display:block;">Lihat Keranjang</a>
      <button class="btn-pop-secondary" onclick="tutupPopup()">Lanjut Belanja</button>
    </div>
  </div>
</div>

<!-- ============ HERO ============ -->
@if(empty(request('q')))
<section class="p-hero">
  <div class="p-hero-eyebrow">✨ Menu Spesial </div>
  <h1>Rasakan kelezatan <em>seblak sangkuriang</em></h1>
  <p>Disajikan hangat untuk Anda. Pilih menu favorit dan biarkan kami yang melayani.</p>
  <a href="#menu-section" class="btn-hero"><i class="bi bi-arrow-down-circle"></i> Jelajahi Menu</a>
</section>
@else
<div class="p-search-alert">
  <i class="bi bi-search"></i> Hasil pencarian: <strong>"{{ request('q') }}"</strong>
  <a href="{{ route('pelanggan.dashboard') }}" class="reset">&times; Reset</a>
</div>
@endif

<!-- ============ CATEGORY PILLS ============ -->
@php
  $kategoriList = isset($kategoriList) ? array_merge(['Semua'], $kategoriList) : ['Semua'];
  $kategoriAktif = $kategoriParam ?? 'Semua';
@endphp
<div class="p-cats">
  @foreach($kategoriList as $kat)
    <a href="{{ $kat==='Semua' ? route('pelanggan.dashboard') : route('pelanggan.dashboard', ['kategori' => $kat]) }}"
       class="p-cat {{ $kat===$kategoriAktif ? 'active' : '' }}">{{ $kat }}</a>
  @endforeach
</div>

<!-- ============ SECTION HEAD ============ -->
<div class="p-section-head" id="menu-section">
  <div>
    <h2 class="p-section-title">
      @if(!empty(request('q'))) Hasil <em>Pencarian</em>
      @elseif($kategoriAktif !== 'Semua') {{ $kategoriAktif }}
      @else Menu <em>Pilihan</em>
      @endif
    </h2>
    <p class="p-section-sub">Pilihan terbaik untuk hari Anda</p>
  </div>
  @if(isset($produk))
    <div class="p-section-count"><strong>{{ $produk->count() }}</strong> menu tersedia</div>
  @endif
</div>

<!-- ============ PRODUCT GRID ============ -->
<div class="p-grid">
  @if(isset($produk) && $produk->isNotEmpty())
    @foreach($produk as $item)
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
    @endforeach
  @else
    <div style="grid-column:1/-1; text-align:center; padding:3rem 1rem;">
      <div style="font-size:3rem;">🔍</div>
      <p style="color:#aaa; margin-top:.75rem;">Tidak ada menu ditemukan.</p>
      @if(!empty(request('q')))
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah btn-sm">Lihat Semua Menu</a>
      @endif
    </div>
  @endif
</div>

{{-- ── REKOMENDASI UNTUK ANDA ── --}}
@if(isset($rekomendasiMenu) && $rekomendasiMenu->isNotEmpty())
<div class="px-3 pb-4">
  <hr style="border-color:#f0d0d0; margin:0 0 1.25rem;">

  {{-- Section header --}}
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
    <div style="display:flex; align-items:center; gap:.5rem;">
      <div style="width:32px; height:32px; background:linear-gradient(135deg,#8b1a1a,#c0392b); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <span style="font-size:.95rem;">✨</span>
      </div>
      <div>
        <div style="font-weight:800; font-size:.97rem; color:#1a1a1a; line-height:1.2;">Rekomendasi Untuk Anda</div>
        <div style="font-size:.72rem; color:#bbb; margin-top:.05rem;">Menu pilihan berdasarkan selera Anda</div>
      </div>
    </div>
    <span style="font-size:.7rem; font-weight:600; color:var(--merah); background:#fdf0f0; border:1px solid #f0d0d0; padding:.2rem .7rem; border-radius:2rem;">
      {{ $rekomendasiMenu->count() }} Menu
    </span>
  </div>

  {{-- Horizontal scroll cards --}}
  <div style="display:flex; gap:.85rem; overflow-x:auto; padding-bottom:.5rem; scroll-snap-type:x mandatory; scrollbar-width:none; -ms-overflow-style:none;">
    @foreach($rekomendasiMenu as $item)
      <a href="{{ route('pelanggan.menu.show', $item->id_menu) }}"
         style="flex:0 0 150px; text-decoration:none; scroll-snap-align:start;">
        <div style="background:white; border-radius:14px; overflow:hidden; border:1.5px solid #f5e0e0; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:transform .18s, box-shadow .18s; height:100%;"
             onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 22px rgba(139,26,26,.15)'"
             onmouseout="this.style.transform='';this.style.boxShadow='0 2px 10px rgba(0,0,0,.07)'">

          {{-- Gambar --}}
          <div style="position:relative; height:110px; overflow:hidden; background:#fdf0f0;">
            @if($item->gambar_menu)
              <img src="{{ asset('storage/'.$item->gambar_menu) }}"
                   alt="{{ $item->nama_menu }}"
                   loading="lazy"
                   style="width:100%; height:100%; object-fit:cover;">
            @else
              <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:2.5rem;">🍲</div>
            @endif
            {{-- Badge --}}
            <div style="position:absolute; top:.4rem; left:.4rem; background:#8b1a1a; color:white; font-size:.55rem; font-weight:700; padding:2px 6px; border-radius:2rem; line-height:1.5;">
              ⭐ Rekomendasi
            </div>
          </div>

          {{-- Info --}}
          <div style="padding:.6rem .7rem .75rem;">
            <div style="font-size:.62rem; color:#bbb; text-transform:uppercase; letter-spacing:.3px; margin-bottom:.15rem;">{{ $item->kategori_menu }}</div>
            <div style="font-weight:700; font-size:.83rem; color:#1a1a1a; line-height:1.3; margin-bottom:.35rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ $item->nama_menu }}</div>
            <div style="font-weight:800; font-size:.88rem; color:#8b1a1a; margin-bottom:.5rem;">Rp {{ number_format($item->harga_menu, 0, ',', '.') }}</div>
            <div style="background:linear-gradient(135deg,#8b1a1a,#a32020); color:white; border:none; border-radius:8px; width:100%; padding:.35rem 0; font-size:.73rem; font-weight:700; text-align:center; cursor:pointer;">
              Pesan
            </div>
          </div>
        </div>
      </a>
    @endforeach
  </div>
  <style>.px-3 div[style*="overflow-x:auto"]::-webkit-scrollbar{display:none}</style>
</div>
@endif

{{-- ── END REKOMENDASI ── --}}

<script>
function tutupPopup() { document.getElementById('popupSukses').classList.remove('show'); }

document.querySelectorAll('.form-addcart').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    const nama = form.closest('.product-card')?.querySelector('.product-name')?.textContent ?? 'Menu';
    btn.textContent = '✓ Ditambahkan';
    btn.disabled = true;

    fetch(form.action, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(new FormData(form))
    }).then(r => {
      if (r.ok || r.redirected) {
        document.getElementById('popupTitle').textContent = nama + ' ditambahkan';
        document.getElementById('popupMsg').textContent = 'Item berhasil masuk ke keranjang Anda.';
        document.getElementById('popupSukses').classList.add('show');
        setTimeout(() => { btn.textContent = '+ Tambah ke Keranjang'; btn.disabled = false; }, 2000);
      }
    }).catch(() => { form.submit(); });
  });
});

setTimeout(() => { const t = document.getElementById('pToast'); if (t) t.style.display = 'none'; }, 4000);
</script>

</body>
</html>

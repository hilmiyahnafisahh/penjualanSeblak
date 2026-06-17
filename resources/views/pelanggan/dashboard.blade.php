<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-pelanggan.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
      <div class="p-card">
        <div class="p-card-media">
          @if($item->gambar_menu)
            <img src="{{ asset('storage/'.$item->gambar_menu) }}" alt="{{ $item->nama_menu }}" loading="lazy">
          @else
            <div class="emoji">🍲</div>
          @endif
        </div>
        <div class="p-card-body">
          <div class="p-card-kategori">{{ $item->kategori_menu }}</div>
          <div class="p-card-name">{{ $item->nama_menu }}</div>
          <div class="p-card-price"><span class="currency">Rp</span> {{ number_format($item->harga_menu, 0, ',', '.') }}</div>
          @if(strtolower($item->kategori_menu) === 'makanan')
            <a href="{{ route('pelanggan.pilihTopping', $item->id_menu) }}" class="btn-add is-pilih" style="text-decoration:none; text-align:center; display:block;">Pilih &amp; Pesan</a>
          @else
            <form action="{{ route('pelanggan.tambahKeranjang') }}" method="POST" class="form-addcart">
              @csrf
              <input type="hidden" name="id_produk" value="{{ $item->id_menu }}">
              <input type="hidden" name="qty" value="1">
              <button type="submit" class="btn-add">+ Tambah ke Keranjang</button>
            </form>
          @endif
        </div>
      </div>
    @endforeach
  @else
    <div class="p-empty">
      <div class="emoji">🔍</div>
      <p>Belum ada menu yang cocok dengan pencarian Anda.</p>
      @if(!empty(request('q')))
        <a href="{{ route('pelanggan.dashboard') }}" class="btn-pop-primary" style="text-decoration:none; display:inline-block; padding:0.6rem 1.5rem;">Lihat Semua Menu</a>
      @endif
    </div>
  @endif
</div>

<script>
function tutupPopup() { document.getElementById('popupSukses').classList.remove('show'); }

document.querySelectorAll('.form-addcart').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    const nama = form.closest('.p-card').querySelector('.p-card-name').textContent;
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

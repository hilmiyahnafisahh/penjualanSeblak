<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --merah: #8b1a1a;
            --merah-gelap: #600e0e;
            --bg: #fdf0f0;
        }
        body { background: var(--bg); font-family: 'Segoe UI', sans-serif; }
        header { background: white; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.06); }

        /* Navbar */
        .btn-merah { background: var(--merah); color: white; border: none; }
        .btn-merah:hover { background: var(--merah-gelap); color: white; }
        .nav-pill-custom .nav-link { color: var(--merah); border-radius: 2rem; font-size: .875rem; }
        .nav-pill-custom .nav-link.active { background: var(--merah); color: white; }

        /* Keranjang badge */
        .cart-btn { position: relative; }
        .cart-badge {
            position: absolute; top: -6px; right: -6px;
            background: var(--merah); color: white;
            border-radius: 50%; font-size: .65rem;
            width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }

        /* Kategori pills */
        .kategori-pill {
            background: white; border: 1.5px solid #e5e5e5;
            border-radius: 2rem; padding: .35rem 1rem;
            font-size: .85rem; cursor: pointer; transition: all .2s;
            color: #444; white-space: nowrap;
        }
        .kategori-pill:hover, .kategori-pill.active {
            background: var(--merah); color: white; border-color: var(--merah);
        }

        /* Card produk */
        .product-card {
            background: white; border-radius: 1rem;
            border: none; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.1); }
        .product-img-wrap {
            position: relative; overflow: hidden;
            background: #f9f9f9; aspect-ratio: 1/1;
        }
        .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .product-img-wrap .badge-stok {
            position: absolute; top: 8px; left: 8px;
            background: var(--merah); color: white;
            font-size: .7rem; padding: .25rem .5rem; border-radius: .5rem;
        }
        .btn-wishlist {
            position: absolute; top: 8px; right: 8px;
            background: white; border: none; border-radius: 50%;
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.1); cursor: pointer;
            transition: all .2s;
        }
        .btn-wishlist:hover { background: #ffe0e0; }
        .btn-wishlist.active svg { fill: var(--merah); stroke: var(--merah); }

        .product-body { padding: .85rem; }
        .product-name { font-weight: 600; font-size: .95rem; margin-bottom: .2rem; color: #1a1a1a; }
        .product-price { font-weight: 700; font-size: 1.05rem; color: var(--merah); }
        .product-meta { font-size: .75rem; color: #888; }
        .star { color: #f5a623; font-size: .8rem; }

        /* Qty control */
        .qty-control {
            display: flex; align-items: center; gap: .35rem;
        }
        .qty-btn {
            width: 28px; height: 28px; border-radius: 50%;
            border: 1.5px solid #ddd; background: white;
            font-size: 1rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .15s;
        }
        .qty-btn:hover { border-color: var(--merah); color: var(--merah); }
        .qty-num { font-size: .9rem; font-weight: 600; min-width: 20px; text-align: center; }
        .btn-addcart {
            background: var(--merah); color: white; border: none;
            border-radius: .5rem; font-size: .8rem; padding: .35rem .75rem;
            font-weight: 600; transition: background .15s;
        }
        .btn-addcart:hover { background: var(--merah-gelap); }

        /* Keranjang dropdown */
        .keranjang-total { font-size: .8rem; line-height: 1.2; }

        /* Section title */
        .section-title { font-weight: 700; font-size: 1.2rem; color: #1a1a1a; }
        .section-divider { border-top: 2px solid #f0d0d0; margin: .5rem 0 1.25rem; }

        /* Search */
        .search-wrap { position: relative; }
        .search-wrap input { border-radius: 2rem; border: 1.5px solid #e0e0e0; padding: .5rem 1rem .5rem 2.5rem; font-size: .9rem; }
        .search-wrap input:focus { border-color: var(--merah); box-shadow: 0 0 0 3px rgba(139,26,26,.1); outline: none; }
        .search-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #aaa; }
    </style>
</head>
<body>

{{-- SVG Icons --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display:none;">
  <defs>
    <symbol id="icon-user" viewBox="0 0 24 24">
      <path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/>
    </symbol>
    <symbol id="icon-cart" viewBox="0 0 24 24">
      <path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/>
    </symbol>
    <symbol id="icon-search" viewBox="0 0 24 24">
      <path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/>
    </symbol>
    <symbol id="icon-heart" viewBox="0 0 24 24">
      <path stroke="currentColor" stroke-width="2" fill="none" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </symbol>
  </defs>
</svg>

{{-- Offcanvas Menu --}}
<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header justify-content-center">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-seblak.png') }}" alt="Seblak Sangkuriang" style="width:80px;height:80px;object-fit:contain;border-radius:50%;" class="mb-1">
        <p class="text-muted small">Halo, {{ session('pelanggan_user_name', 'Pelanggan') }}!</p>
    </div>
    <div class="d-grid gap-2">
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah">Dashboard</a>
        <a href="{{ route('pelanggan.pesanan') }}" class="btn btn-outline-secondary">Pesanan Saya</a>
        <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-outline-secondary">Riwayat Pembayaran</a>
        <hr>
        <form action="{{ route('pelanggan.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger w-100">Keluar</button>
        </form>
    </div>
  </div>
</div>

{{-- Header --}}
<header>
  <div class="container-fluid px-3 px-lg-4">
    {{-- Row 1: Logo | Search | User + Cart --}}
    <div class="row py-3 align-items-center">
      <div class="col-auto d-flex align-items-center gap-2">
        <img src="{{ asset('images/logo-seblak.png') }}" alt="Seblak Sangkuriang" style="height:48px;width:48px;object-fit:contain;border-radius:50%;">
        <div class="d-none d-sm-block" style="line-height:1.2;">
          <div style="font-size:.7rem;color:#888;letter-spacing:.05em;">Welcome to</div>
          <div style="font-size:1rem;font-weight:700;color:#8b1a1a;">Seblak Sangkuriang</div>
        </div>
      </div>
      <div class="col d-none d-md-block px-4">
        <div class="search-wrap">
          <svg class="search-icon" width="16" height="16"><use xlink:href="#icon-search"></use></svg>
          <input type="text" class="form-control" placeholder="Cari menu seblak...">
        </div>
      </div>
      <div class="col-auto d-flex align-items-center gap-3">
        {{-- Keranjang --}}
        <div class="dropdown">
          <button class="btn btn-light position-relative cart-btn rounded-circle p-2" data-bs-toggle="dropdown">
            <svg width="22" height="22"><use xlink:href="#icon-cart"></use></svg>
            @php $totalKeranjang = collect($keranjang ?? [])->sum('qty') @endphp
            @if($totalKeranjang > 0)
              <span class="cart-badge">{{ $totalKeranjang }}</span>
            @endif
          </button>
          <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="min-width:260px;">
            <h6 class="fw-bold mb-2">Keranjang Anda</h6>
            @if(empty($keranjang))
              <p class="text-muted small mb-2">Keranjang masih kosong.</p>
            @else
              @foreach($keranjang as $item)
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                  <span>{{ $item['nama'] }} × {{ $item['qty'] }}</span>
                  <span class="fw-bold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
              @endforeach
              <hr class="my-2">
              <div class="d-flex justify-content-between fw-bold small mb-2">
                <span>Total</span>
                <span class="text-danger">Rp {{ number_format(collect($keranjang)->sum('subtotal'), 0, ',', '.') }}</span>
              </div>
              <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-merah btn-sm w-100">Lihat Keranjang</a>
            @endif
          </div>
        </div>
        {{-- Total harga --}}
        <div class="keranjang-total text-end d-none d-sm-block">
          <div class="text-muted" style="font-size:.75rem;">Keranjang Anda ▾</div>
          <div class="fw-bold" style="font-size:.95rem;">
            Rp {{ number_format(collect($keranjang ?? [])->sum('subtotal'), 0, ',', '.') }}
          </div>
        </div>
        {{-- User --}}
        <span class="text-muted d-none d-lg-block small">{{ session('pelanggan_user_name', 'Pelanggan') }}</span>
        <a href="#" class="rounded-circle bg-light p-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="22" height="22"><use xlink:href="#icon-user"></use></svg>
        </a>
      </div>
    </div>

    {{-- Search mobile --}}
    <div class="d-md-none pb-2">
      <div class="search-wrap">
        <svg class="search-icon" width="16" height="16"><use xlink:href="#icon-search"></use></svg>
        <input type="text" class="form-control" placeholder="Cari menu seblak...">
      </div>
    </div>

    {{-- Navbar --}}
    <div class="border-top py-2">
      <ul class="nav nav-pills nav-pill-custom gap-2">
        <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link active">Dashboard</a></li>
        <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link">Pesanan Saya</a></li>
        <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link">Riwayat Pembayaran</a></li>
      </ul>
    </div>
  </div>
</header>

{{-- Konten Utama --}}
<section class="py-4">
  <div class="container-fluid px-3 px-lg-4">

    {{-- Filter Kategori --}}
    <div class="d-flex gap-2 mb-4 overflow-auto pb-1" style="scrollbar-width:none;">
      @php
        $kategoriList = isset($kategoriList) ? array_merge(['Semua'], $kategoriList) : ['Semua'];
        $kategoriAktif = $kategoriParam ?? 'Semua';
      @endphp
      @foreach($kategoriList as $kat)
        <a href="{{ route('pelanggan.dashboard', ['kategori' => $kat]) }}"
           class="kategori-pill text-decoration-none {{ $kategoriAktif === $kat ? 'active' : '' }}">
          {{ $kat }}
        </a>
      @endforeach
    </div>

    {{-- Grid Produk --}}
    <h5 class="section-title mb-1">
      {{ $kategoriAktif === 'Semua' ? 'Produk Terbaru' : $kategoriAktif }}
    </h5>
    <div class="section-divider"></div>

    @if(isset($produk) && $produk->isNotEmpty())
      <div class="row g-3">
        @foreach($produk as $item)
          <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card product-card h-100">
              <div class="product-img-wrap">
                <img src="{{ $item->gambar_menu ? asset('storage/'.$item->gambar_menu) : asset('images/placeholder-seblak.jpg') }}"
                     alt="{{ $item->nama_menu }}" loading="lazy">
                <button class="btn-wishlist" title="Simpan ke wishlist">
                  <svg width="16" height="16"><use xlink:href="#icon-heart"></use></svg>
                </button>
              </div>
              <div class="product-body">
                <div class="product-name">{{ $item->nama_menu }}</div>
                <div class="product-meta mb-1">
                  {{ $item->kategori_menu }}
                  <span class="star ms-1">★</span>
                  <span>{{ number_format($item->rating ?? 0, 2) }}</span>
                </div>
                <div class="product-price mb-2">Rp {{ number_format($item->harga_menu, 0, ',', '.') }}</div>
                @if(strtolower($item->kategori_menu) === 'makanan')
                  <a href="{{ route('pelanggan.menu.show', $item->id_menu) }}" class="btn-addcart mt-3 d-inline-block text-center w-100">Pilih Rincian Pesanan</a>
                @else
                  <form action="{{ route('pelanggan.keranjang.tambah') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="id_produk" value="{{ $item->id_menu }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn-addcart w-100">Tambah ke Keranjang</button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      {{-- Demo / Placeholder jika belum ada data --}}
      <div class="row g-3">
        @php
          $demoMenu = [
            ['nama' => 'Seblak Original Pedas', 'harga' => 15000, 'stok' => 10, 'rating' => 4.8, 'emoji' => '🌶️'],
            ['nama' => 'Seblak Mie Kuah', 'harga' => 18000, 'stok' => 8, 'rating' => 4.5, 'emoji' => '🍜'],
            ['nama' => 'Seblak Ceker Pedas', 'harga' => 20000, 'stok' => 5, 'rating' => 4.9, 'emoji' => '🍗'],
            ['nama' => 'Seblak Kerupuk Kering', 'harga' => 12000, 'stok' => 15, 'rating' => 4.3, 'emoji' => '🥨'],
            ['nama' => 'Seblak Seafood Komplit', 'harga' => 25000, 'stok' => 6, 'rating' => 4.7, 'emoji' => '🦐'],
            ['nama' => 'Seblak Sosis Spesial', 'harga' => 22000, 'stok' => 9, 'rating' => 4.6, 'emoji' => '🌭'],
            ['nama' => 'Teh Manis Hangat', 'harga' => 5000, 'stok' => 20, 'rating' => 4.2, 'emoji' => '🍵'],
            ['nama' => 'Es Jeruk Segar', 'harga' => 7000, 'stok' => 18, 'rating' => 4.4, 'emoji' => '🍊'],
          ];
        @endphp
        @foreach($demoMenu as $i => $demo)
          <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card product-card h-100">
              <div class="product-img-wrap" style="background:{{ ['#fde8e8','#fef3e8','#e8f4fd','#e8fde8','#f3e8fd','#fdf3e8','#e8fdfd','#fde8f3'][$i] }}; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:3.5rem;">{{ $demo['emoji'] }}</span>
                <button class="btn-wishlist" title="Simpan ke wishlist">
                  <svg width="16" height="16"><use xlink:href="#icon-heart"></use></svg>
                </button>
              </div>
              <div class="product-body">
                <div class="product-name">{{ $demo['nama'] }}</div>
                <div class="product-meta mb-1">
                  {{ $demo['stok'] }} UNIT
                  <span class="star ms-1">★</span>
                  <span>{{ $demo['rating'] }}</span>
                </div>
                <div class="product-price mb-2">Rp {{ number_format($demo['harga'], 0, ',', '.') }}</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="qty-control">
                    <button class="qty-btn" onclick="ubahQty(this, -1)">−</button>
                    <span class="qty-num">1</span>
                    <button class="qty-btn" onclick="ubahQty(this, 1)">+</button>
                  </div>
                  <button class="btn-addcart">Add to Cart</button>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function ubahQty(btn, delta) {
    const wrap = btn.closest('.qty-control');
    const numEl = wrap.querySelector('.qty-num');
    let val = parseInt(numEl.textContent) + delta;
    if (val < 1) val = 1;
    numEl.textContent = val;
    const form = wrap.closest('form');
    if (form) {
        const qtyInput = form.querySelector('.qty-hidden');
        if (qtyInput) qtyInput.value = val;
    }
}

// Wishlist toggle
document.querySelectorAll('.btn-wishlist').forEach(btn => {
    btn.addEventListener('click', () => btn.classList.toggle('active'));
});
</script>
</body>
</html>
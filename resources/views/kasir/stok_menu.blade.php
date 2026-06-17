<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stok & Menu | Kasir</title>
  <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <aside class="sidebar p-4 flex-shrink-0" style="width:280px;">
        <div class="mb-5 d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:42px;height:42px;object-fit:contain;background:#fff;border-radius:50%;padding:2px;">
            <div>
                <h3 class="fw-bold mb-0">Seblak Sangkuriang</h3>
                <div style="color:#f5c6c6;font-size:.85rem;">Panel Kasir</div>
            </div>
        </div>
        <div class="mb-4">
            <a href="{{ route('kasir.dashboard') }}" class="d-block p-3 rounded-3 mb-2">Dashboard</a>
            <a href="{{ route('kasir.pesanan') }}" class="d-block p-3 rounded-3 mb-2">Pesanan Masuk</a>
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2">Pembayaran</a>
            <a href="{{ route('kasir.stok_menu') }}" class="d-block p-3 rounded-3 mb-2 active">Stok & Menu</a>
        </div>
      </div>
      <form action="{{ route('kasir.logout') }}" method="POST">
        @csrf
        <button type="submit" class="k-logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</button>
      </form>
    </div>
  </aside>

  <main class="kasir-main">
    <div class="k-pageheader">
      <div>
        <h1>Stok &amp; Menu</h1>
        <p>Kelola daftar menu dan pantau stok barang.</p>
      </div>
      <div class="k-timestamp">
        <span class="k-status-dot"></span>
        <span>{{ now()->translatedFormat('l, d M Y — H:i') }}</span>
      </div>
    </div>

    <div class="k-tabs">
      <a class="k-tab {{ $tab === 'menu' ? 'active' : '' }}" href="{{ route('kasir.stok_menu', ['tab' => 'menu']) }}">
        <i class="bi bi-journal-richtext"></i> Menu
      </a>
      <a class="k-tab {{ $tab === 'barang' ? 'active' : '' }}" href="{{ route('kasir.stok_menu', ['tab' => 'barang']) }}">
        <i class="bi bi-box-seam"></i> Barang
      </a>
    </div>

    @if($tab === 'menu')
      <section class="k-panel">
        <div class="k-panel-head">
          <h2>Daftar Menu</h2>
          <span class="k-badge">{{ $menuList->count() }} menu</span>
        </div>

        @if($menuList->isEmpty())
          <div class="k-empty">
            <i class="bi bi-journal-x" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
            Tidak ada menu tersedia.
          </div>
        @else
          <div class="k-table-wrap">
            <table class="k-table">
              <thead>
                <tr>
                  <th style="width:50px;">No</th>
                  <th>Nama Menu</th>
                  <th>Kategori</th>
                  <th style="text-align:right;">Harga</th>
                </tr>
              </thead>
              <tbody>
                @foreach($menuList as $i => $menu)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="k-link">{{ $menu->nama_menu }}</span></td>
                    <td><span class="k-badge is-info">{{ $menu->kategori_menu }}</span></td>
                    <td class="k-num" style="text-align:right;">Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </section>
    @else
      <section class="k-panel">
        <div class="k-panel-head">
          <h2>Daftar Barang</h2>
          <span class="k-badge">{{ $barangList->count() }} barang</span>
        </div>

        @if($barangList->isEmpty())
          <div class="k-empty">
            <i class="bi bi-box" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
            Tidak ada barang tersedia.
          </div>
        @else
          <div class="k-table-wrap">
            <table class="k-table">
              <thead>
                <tr>
                  <th style="width:50px;">No</th>
                  <th>Nama Barang</th>
                  <th>Stok</th>
                  <th style="text-align:right;">Harga Jual</th>
                </tr>
              </thead>
              <tbody>
                @foreach($barangList as $i => $barang)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="k-link">{{ $barang->nama_barang }}</span></td>
                    <td>
                      @if($barang->stok <= 0)
                        <span class="k-badge is-danger">Habis</span>
                      @elseif($barang->stok <= 5)
                        <span class="k-badge is-warn">{{ $barang->stok }} <span class="k-sub">Hampir habis</span></span>
                      @else
                        <span class="k-badge is-success">{{ $barang->stok }}</span>
                      @endif
                    </td>
                    <td class="k-num" style="text-align:right;">Rp {{ number_format($barang->harga_jual ?? 0, 0, ',', '.') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </section>
    @endif
  </main>
</div>

</body>
</html>

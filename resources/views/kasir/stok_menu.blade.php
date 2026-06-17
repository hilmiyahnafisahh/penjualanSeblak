<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok &amp; Menu — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="kasir-shell">

    <aside class="kasir-sidebar">
        <div class="k-brand">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo" style="border-radius:50%;">
            <div>
                <div class="k-name">Seblak Sangkuriang</div>
                <div class="k-sub">Panel Kasir</div>
            </div>
        </div>
        <div class="k-nav-label">Operasional</div>
        <a href="{{ route('kasir.dashboard') }}" class="k-nav-link"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="{{ route('kasir.pesanan') }}" class="k-nav-link"><i class="bi bi-bag-check"></i> Pesanan Masuk</a>
        <a href="{{ route('kasir.pembayaran') }}" class="k-nav-link"><i class="bi bi-credit-card-2-front"></i> Pembayaran</a>
        <a href="{{ route('kasir.stok_menu') }}" class="k-nav-link active"><i class="bi bi-box-seam"></i> Stok &amp; Menu</a>
        <div class="k-sidebar-footer">
            <div class="k-user-card">
                <div class="k-user-avatar">{{ strtoupper(substr(session('kasir_user_name','K'),0,1)) }}</div>
                <div class="k-user-meta">
                    <div class="role">Kasir</div>
                    <div class="name">{{ session('kasir_user_name','Kasir') }}</div>
                </div>
            </div>
            <form action="{{ route('kasir.logout') }}" method="POST">
                @csrf
                <button type="submit" class="k-logout-btn"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </div>
    </aside>

    <main class="kasir-main">
        <div class="k-pageheader">
            <div>
                <h1>Stok &amp; Menu</h1>
                <p class="subtitle">Kelola daftar menu dan pantau stok barang.</p>
            </div>
            <div class="k-timestamp">
                <span class="k-status-dot"></span>
                {{ now()->translatedFormat('l, d M Y — H:i') }}
            </div>
        </div>

        <div class="k-tabs">
            <a class="k-tab {{ $tab === 'menu' ? 'active' : '' }}"
               href="{{ route('kasir.stok_menu', ['tab' => 'menu']) }}">
                <i class="bi bi-journal-richtext"></i> Menu
            </a>
            <a class="k-tab {{ $tab === 'barang' ? 'active' : '' }}"
               href="{{ route('kasir.stok_menu', ['tab' => 'barang']) }}">
                <i class="bi bi-box-seam"></i> Barang
            </a>
        </div>

        @if($tab === 'menu')
            <div class="k-panel">
                <div class="k-panel-head">
                    <div>
                        <h2>Daftar Menu</h2>
                        <p class="panel-sub">{{ $menuList->count() }} menu tersedia.</p>
                    </div>
                </div>
                @if($menuList->isEmpty())
                    <div class="k-empty">Tidak ada menu tersedia.</div>
                @else
                    <table class="k-table">
                        <thead>
                            <tr>
                                <th style="width:48px;">#</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th style="text-align:right;">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menuList as $i => $menu)
                                <tr>
                                    <td style="color:var(--ink-400);font-weight:600;">{{ $i + 1 }}</td>
                                    <td style="font-weight:600;color:var(--ink-900);">{{ $menu->nama_menu }}</td>
                                    <td><span class="k-badge is-info">{{ $menu->kategori_menu }}</span></td>
                                    <td class="k-num" style="text-align:right;">Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @else
            <div class="k-panel">
                <div class="k-panel-head">
                    <div>
                        <h2>Daftar Barang</h2>
                        <p class="panel-sub">{{ $barangList->count() }} barang tersedia.</p>
                    </div>
                </div>
                @if($barangList->isEmpty())
                    <div class="k-empty">Tidak ada barang tersedia.</div>
                @else
                    <table class="k-table">
                        <thead>
                            <tr>
                                <th style="width:48px;">#</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th style="text-align:right;">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barangList as $i => $barang)
                                <tr>
                                    <td style="color:var(--ink-400);font-weight:600;">{{ $i + 1 }}</td>
                                    <td style="font-weight:600;color:var(--ink-900);">{{ $barang->nama_barang }}</td>
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
                @endif
            </div>
        @endif
    </main>
</div>
</body>
</html>

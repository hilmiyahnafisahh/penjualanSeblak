<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok & Menu | Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        :root { --merah: #8b1a1a; --merah-light: #c85a57; --sidebar-bg: #3d0c0c; --sidebar-hover: rgba(255,255,255,.1); }
        body { background: #fdf0f0; }
        .sidebar { min-height: 100vh; background: var(--sidebar-bg); color: #f5e6e6; border-right: none; }
        .sidebar a { color: #f5c6c6; text-decoration: none; }
        .sidebar a:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.15); color: #fff; font-weight: 600; border-left: 3px solid #f87171; }
        .table-card { border-radius: 1rem; }
        .nav-tabs .nav-link.active { border-bottom-color: var(--merah); color: var(--merah); }
        .btn-merah { background: var(--merah); color: #fff; border-radius: .6rem; border: none; padding: .35rem .7rem; box-shadow: 0 6px 18px rgba(139,26,26,.06); }
        .btn-outline-merah { background: transparent; color: var(--merah); border: 1.5px solid var(--merah); border-radius: .6rem; padding: .25rem .6rem; }
    </style>
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
        <div class="mt-auto pt-4 border-top" style="border-color:rgba(255,255,255,.15)!important;">
            <div class="mb-2" style="color:#f5c6c6;font-size:.85rem;">Login sebagai</div>
            <div class="fw-semibold text-white">{{ session('kasir_user_name', 'Kasir') }}</div>
            <form action="{{ route('kasir.logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="flex-grow-1 p-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">Stok & Menu</h1>
                <p class="text-muted mb-0">Kelola menu dan lihat stok barang.</p>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'menu' ? 'active' : '' }}" href="{{ route('kasir.stok_menu', ['tab' => 'menu']) }}">Menu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'barang' ? 'active' : '' }}" href="{{ route('kasir.stok_menu', ['tab' => 'barang']) }}">Barang</a>
            </li>
        </ul>

        @if($tab === 'menu')
            <div class="card shadow-sm p-4 table-card">
                <h2 class="h5 mb-3">Daftar Menu</h2>
                @if($menuList->isEmpty())
                    <div class="text-center py-5 text-muted">Tidak ada menu.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>No</th>
                                    <th>Nama Menu</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menuList as $menu)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $menu->nama_menu }}</td>
                                        <td>{{ $menu->kategori_menu }}</td>
                                        <td>Rp {{ number_format($menu->harga_menu, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @else
            <div class="card shadow-sm p-4 table-card">
                <h2 class="h5 mb-3">Daftar Barang</h2>
                @if($barangList->isEmpty())
                    <div class="text-center py-5 text-muted">Tidak ada barang.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Stok</th>
                                    <th>Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangList as $barang)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $barang->nama_barang }}</td>
                                        <td>
                                            @if($barang->stok <= 0)
                                                <span class="badge bg-danger">Habis</span>
                                            @elseif($barang->stok <= 5)
                                                <span class="badge bg-warning text-dark">{{ $barang->stok }} <small>(Hampir Habis)</small></span>
                                            @else
                                                <span class="badge bg-success">{{ $barang->stok }}</span>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok & Menu | Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body { background: #fdf0f0; }
        .sidebar { min-height: 100vh; background: #8b1a1a; color: white; }
        .sidebar a { color: #f0d7d7; }
        .sidebar a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .table-card { border-radius: 1rem; }
        .nav-tabs .nav-link.active { border-bottom-color: #8b1a1a; color: #8b1a1a; }
    </style>
</head>
<body>
<div class="d-flex">
    <aside class="sidebar p-4 flex-shrink-0" style="width:280px;">
        <div class="mb-5">
            <h3 class="fw-bold">🌶️ SEBLAK</h3>
            <div class="text-muted">Panel Kasir</div>
        </div>
        <div class="mb-4">
            <a href="{{ route('kasir.dashboard') }}" class="d-block p-3 rounded-3 mb-2">Dashboard</a>
            <a href="{{ route('kasir.pesanan') }}" class="d-block p-3 rounded-3 mb-2">Pesanan Masuk</a>
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2">Pembayaran</a>
            <a href="{{ route('kasir.stok_menu') }}" class="d-block p-3 rounded-3 mb-2 active">Stok & Menu</a>
        </div>
        <div class="mt-auto pt-4 border-top border-white-25">
            <div class="mb-2">Login sebagai</div>
            <div class="fw-semibold">{{ session('kasir_user_name', 'Kasir') }}</div>
            <form action="{{ route('kasir.logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100">Keluar</button>
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
                                        <td>{{ $barang->stok }}</td>
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

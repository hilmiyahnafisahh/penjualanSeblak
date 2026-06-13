@php
    $statusLabels = [
        'belum_bayar' => 'Belum Bayar',
        'lunas' => 'Lunas',
        'semua' => 'Semua',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran | Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body { background: #fdf0f0; }
        .sidebar { min-height: 100vh; background: #8b1a1a; color: white; }
        .sidebar a { color: #f0d7d7; }
        .sidebar a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .table-card { border-radius: 1rem; }
        .badge-status { font-size: .85rem; }
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
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2 active">Pembayaran</a>
            <a href="{{ route('kasir.stok_menu') }}" class="d-block p-3 rounded-3 mb-2">Stok & Menu</a>
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
                <h1 class="h3">Konfirmasi Pembayaran</h1>
                <p class="text-muted mb-0">Lihat status pembayaran pesanan.</p>
            </div>
        </div>

        <div class="mb-4">
            @foreach($statusLabels as $key => $label)
                <a href="{{ route('kasir.pembayaran', ['status' => $key]) }}" class="btn btn-sm {{ $statusParam === $key ? 'btn-danger' : 'btn-outline-secondary' }} me-2 mb-2">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card shadow-sm p-4 table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Daftar Pembayaran</h2>
                <span class="badge bg-secondary">{{ $pembayaran->count() }} data</span>
            </div>

            @if($pembayaran->isEmpty())
                <div class="text-center py-5 text-muted">
                    Tidak ada data pembayaran
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>No</th>
                                <th>No Pembayaran</th>
                                <th>No Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembayaran as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->id_pembayaran }}</td>
                                    <td>{{ $item->pemesanan?->id_pesanan ?? '-' }}</td>
                                    <td>{{ $item->pemesanan?->Pelanggan?->nama_pelanggan ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status_pembayaran === 'lunas' ? 'success' : 'warning' }} text-dark badge-status">
                                            {{ ucfirst($item->status_pembayaran) }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

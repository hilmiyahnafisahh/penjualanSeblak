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
        :root { --merah: #8b1a1a; --merah-light: #c85a57; --sidebar-bg: #3d0c0c; --sidebar-hover: rgba(255,255,255,.1); }
        body { background: #fdf0f0; }
        .sidebar { min-height: 100vh; background: var(--sidebar-bg); color: #f5e6e6; border-right: none; }
        .sidebar a { color: #f5c6c6; text-decoration: none; }
        .sidebar a:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.15); color: #fff; font-weight: 600; border-left: 3px solid #f87171; }
        .table-card { border-radius: 1rem; }
        .badge-status { font-size: .85rem; }
        .btn-merah { background: var(--merah); color: #fff; border-radius: .6rem; border: none; padding: .35rem .7rem; box-shadow: 0 6px 18px rgba(139,26,26,.06); }
        .btn-outline-merah { background: transparent; color: var(--merah); border: 1.5px solid var(--merah); border-radius: .6rem; padding: .25rem .6rem; }
        .btn-merah:hover { background: var(--merah-light); color: #fff; }
    </style>
</head>
<body>
<div class="d-flex">
    <aside class="sidebar p-4 flex-shrink-0" style="width:280px;">
        <div class="mb-5 d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:42px;height:42px;object-fit:contain;">
            <div>
                <h3 class="fw-bold mb-0">Seblak Sangkuriang</h3>
                <div style="color:#f5c6c6;font-size:.85rem;">Panel Kasir</div>
            </div>
        </div>
        <div class="mb-4">
            <a href="{{ route('kasir.dashboard') }}" class="d-block p-3 rounded-3 mb-2">Dashboard</a>
            <a href="{{ route('kasir.pesanan') }}" class="d-block p-3 rounded-3 mb-2">Pesanan Masuk</a>
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2 active">Pembayaran</a>
            <a href="{{ route('kasir.stok_menu') }}" class="d-block p-3 rounded-3 mb-2">Stok & Menu</a>
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
                <h1 class="h3">Konfirmasi Pembayaran</h1>
                <p class="text-muted mb-0">Lihat status pembayaran pesanan.</p>
            </div>
        </div>

        <div class="mb-4">
            @foreach($statusLabels as $key => $label)
                <a href="{{ route('kasir.pembayaran', ['status' => $key]) }}" class="btn btn-sm {{ $statusParam === $key ? 'btn-merah' : 'btn-outline-merah' }} me-2 mb-2">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card shadow-sm p-4 table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Daftar Pembayaran</h2>
                <span class="badge bg-secondary">{{ $pembayaran->count() }} data</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

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
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Aksi</th>
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
                                    <td>{{ optional($item->tanggal_pembayaran)->format('d M Y H:i') ?? optional($item->pemesanan?->tanggal_pemesanan)->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
                                    <td>
                                        @if($item->status_pembayaran === 'pending' && strtolower($item->metode_pembayaran) === 'tunai')
                                            <form action="{{ route('kasir.pembayaran.bayar', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-merah">Bayar Tunai</button>
                                            </form>
                                        @else
                                            -
                                        @endif
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

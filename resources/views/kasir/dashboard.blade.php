@php
    function format_idr($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        :root { --merah: #8b1a1a; --merah-light: #c85a57; --sidebar-bg: #3d0c0c; --sidebar-hover: rgba(255,255,255,.1); }
        body { background: #fdf0f0; }
        .sidebar { min-height: 100vh; background: var(--sidebar-bg); color: #f5e6e6; border-right: none; }
        .sidebar a { color: #f5c6c6; text-decoration: none; }
        .sidebar a:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.15); color: #fff; font-weight: 600; border-left: 3px solid #f87171; }
        .card-status { border: none; border-radius: 1rem; }
        .card-status .bi { font-size: 1.4rem; }
        .badge-status { font-size: .85rem; }
        .table-card { border-radius: 1rem; }
        .btn-merah { background: var(--merah); color: #fff; border-radius: .6rem; border: none; padding: .35rem .7rem; box-shadow: 0 6px 18px rgba(139,26,26,.06); }
        .btn-outline-merah { background: transparent; color: var(--merah); border: 1.5px solid var(--merah); border-radius: .6rem; padding: .25rem .6rem; }
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
            <a href="{{ route('kasir.dashboard') }}" class="d-block p-3 rounded-3 mb-2 active">Dashboard</a>
                <a href="{{ route('kasir.pesanan') }}" class="d-block p-3 rounded-3 mb-2">Pesanan Masuk</a>
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2">Pembayaran</a>
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
                <h1 class="h3">Dashboard</h1>
                <p class="text-muted mb-0">Selamat datang di panel kasir.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted">{{ now()->translatedFormat('l, d F Y H:i') }}</div>
                <span class="badge bg-danger">Online</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-status p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted">Pesanan Pending</div>
                            <div class="h4 mb-0">{{ $pendingCount }}</div>
                        </div>
                        <div class="bg-warning rounded-circle p-2 text-white">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="text-muted">Menunggu konfirmasi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-status p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted">Sedang Diproses</div>
                            <div class="h4 mb-0">{{ $diprosesCount }}</div>
                        </div>
                        <div class="bg-info rounded-circle p-2 text-white">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                    <div class="text-muted">Pesanan diproses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-status p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted">Belum Dibayar</div>
                            <div class="h4 mb-0">{{ $belumBayarCount }}</div>
                        </div>
                        <div class="bg-danger rounded-circle p-2 text-white">
                            <i class="bi bi-credit-card"></i>
                        </div>
                    </div>
                    <div class="text-muted">Menunggu pembayaran</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-status p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted">Pendapatan Hari Ini</div>
                            <div class="h4 mb-0">{{ format_idr($todayRevenue) }}</div>
                        </div>
                        <div class="bg-success rounded-circle p-2 text-white">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="text-muted">Pembayaran lunas</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4 table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 mb-1">Pesanan Masuk Terbaru</h2>
                    <p class="text-muted mb-0">Lihat pesanan terbaru dari pelanggan.</p>
                </div>
                <a href="{{ route('kasir.pesanan') }}" class="btn btn-outline-secondary">Lihat Semua</a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center py-5 text-muted">
                    Tidak ada pesanan pending
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>No</th>
                                <th>No Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->id_pesanan }}</td>
                                    <td>{{ $order->Pelanggan->nama_pelanggan ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_pemesanan === 'selesai' ? 'success' : ($order->status_pemesanan === 'diproses' ? 'info' : 'warning') }} text-dark badge-status">
                                            {{ ucfirst($order->status_pemesanan) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($order->tanggal_pemesanan)->format('d M Y H:i') }}</td>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" />
</body>
</html>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" />
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
<div class="kasir-shell">

    {{-- SIDEBAR --}}
    <aside class="kasir-sidebar">
        <div class="k-brand">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="border-radius:50%;">
            <div>
                <h3 class="fw-bold mb-0">Seblak Sangkuriang</h3>
                <div style="color:#f5c6c6;font-size:.85rem;">Panel Kasir</div>
            </div>
        </div>

        <div class="k-nav-label">Operasional</div>
        <a href="{{ route('kasir.dashboard') }}" class="k-nav-link active">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <a href="{{ route('kasir.pesanan') }}" class="k-nav-link">
            <i class="bi bi-bag-check"></i> Pesanan Masuk
        </a>
        <a href="{{ route('kasir.pembayaran') }}" class="k-nav-link">
            <i class="bi bi-credit-card-2-front"></i> Pembayaran
        </a>
        <a href="{{ route('kasir.stok_menu') }}" class="k-nav-link">
            <i class="bi bi-box-seam"></i> Stok &amp; Menu
        </a>

        <div class="k-sidebar-footer">
            <div class="k-user-card">
                <div class="k-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="k-user-meta">
                    <div class="role">Kasir</div>
                    <div class="name">{{ Auth::user()->name }}</div>
                </div>
            </div>
            <form action="{{ route('kasir.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="kasir-main">
        <div class="k-pageheader">
            <div>
                <h1>Dashboard</h1>
                <p class="subtitle">Selamat datang kembali, {{ explode(' ', Auth::user()->name)[0] }}. Pantau pesanan masuk hari ini di sini.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted">{{ now()->translatedFormat('l, d F Y H:i') }}</div>
                <span class="badge bg-danger">Online</span>
            </div>
        </div>

        <div class="k-stat-grid">
            <div class="k-stat is-warn">
                <div class="k-stat-top">
                    <span class="k-stat-label">Pesanan Pending</span>
                    <span class="k-stat-icon"><i class="bi bi-clock-history"></i></span>
                </div>
                <div class="k-stat-value">{{ $pendingCount ?? 0 }}</div>
                <div class="k-stat-foot">Menunggu konfirmasi</div>
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
                <div class="k-stat-value">{{ $prosesCount ?? 0 }}</div>
                <div class="k-stat-foot">Sedang dimasak</div>
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
                <div class="k-stat-value">{{ $belumBayarCount ?? 0 }}</div>
                <div class="k-stat-foot">Menunggu pembayaran</div>
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
                <div class="k-stat-value">{{ format_idr($pendapatanHariIni ?? 0) }}</div>
                <div class="k-stat-foot">Pembayaran lunas</div>
            </div>
        </div>

        <div class="k-panel">
            <div class="k-panel-head">
                <div>
                    <h2 class="h5 mb-1">Pesanan Masuk Terbaru</h2>
                    <p class="text-muted mb-0">Lihat pesanan terbaru dari pelanggan.</p>
                </div>
                <a href="{{ route('kasir.pesanan') }}" class="btn btn-outline-secondary">Lihat Semua</a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center py-5 text-muted">
                    Tidak ada pesanan
                </div>
            @else
                <table class="k-table">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>No Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th style="text-align:right;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $i => $order)
                            <tr>
                                <td style="color:var(--ink-400); font-weight:600;">{{ $i + 1 }}</td>
                                <td style="font-weight:600; color:var(--ink-900);">{{ $order->id_pesanan ?? '-' }}</td>
                                <td>{{ $order->Pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>
                                    @php
                                        $st = strtolower($order->status_pemesanan ?? '');
                                        $stCls = 'is-info';
                                        if (in_array($st, ['belumdibayar','pending','menunggu'])) $stCls = 'is-warn';
                                        elseif (in_array($st, ['selesai','lunas'])) $stCls = 'is-success';
                                        elseif (in_array($st, ['batal','gagal','ditolak'])) $stCls = 'is-danger';
                                    @endphp
                                    <span class="k-badge {{ $stCls }}">{{ ucfirst($order->status_pemesanan ?? '-') }}</span>
                                </td>
                                <td style="text-align:right; color:var(--ink-500); font-size:0.82rem;">
                                    {{ $order->created_at->translatedFormat('d M Y · H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</div>

</body>
</html>

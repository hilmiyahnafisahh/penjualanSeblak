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
    <title>Dashboard Kasir — Seblak Sangkuriang</title>
    <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="d-flex">
    <aside class="sidebar p-4 flex-shrink-0" style="width:280px;">
        <div class="mb-5 d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:42px;height:42px;object-fit:contain;background:#fff;border-radius:50%;padding:2px;">
            <div>
                <div class="k-name">Seblak Sangkuriang</div>
                <div class="k-sub">Panel Kasir</div>
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
                <button type="submit" class="k-logout-btn">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="kasir-main">
        <div class="k-pageheader">
            <div>
                <h1>Dashboard</h1>
                <p class="subtitle">Selamat datang kembali, {{ explode(' ', Auth::user()->name)[0] }}. Pantau pesanan masuk hari ini di sini.</p>
            </div>
            <div class="k-timestamp">
                <span class="k-status-dot"></span>
                {{ now()->translatedFormat('l, d M Y — H:i') }}
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
            <div class="k-stat is-info">
                <div class="k-stat-top">
                    <span class="k-stat-label">Sedang Diproses</span>
                    <span class="k-stat-icon"><i class="bi bi-arrow-repeat"></i></span>
                </div>
                <div class="k-stat-value">{{ $prosesCount ?? 0 }}</div>
                <div class="k-stat-foot">Sedang dimasak</div>
            </div>
            <div class="k-stat is-danger">
                <div class="k-stat-top">
                    <span class="k-stat-label">Belum Dibayar</span>
                    <span class="k-stat-icon"><i class="bi bi-credit-card"></i></span>
                </div>
                <div class="k-stat-value">{{ $belumBayarCount ?? 0 }}</div>
                <div class="k-stat-foot">Menunggu pembayaran</div>
            </div>
            <div class="k-stat is-success">
                <div class="k-stat-top">
                    <span class="k-stat-label">Pendapatan Hari Ini</span>
                    <span class="k-stat-icon"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="k-stat-value">{{ format_idr($pendapatanHariIni ?? 0) }}</div>
                <div class="k-stat-foot">Pembayaran lunas</div>
            </div>
        </div>

        <div class="k-panel">
            <div class="k-panel-head">
                <div>
                    <h2>Pesanan Masuk Terbaru</h2>
                    <p class="panel-sub">5 pesanan terakhir dari pelanggan.</p>
                </div>
                <a href="{{ route('kasir.pesanan') }}" class="btn-link-orange">Lihat Semua &rarr;</a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="k-empty">Belum ada pesanan masuk.</div>
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
                                <td style="font-weight:600; color:var(--ink-900);">{{ $order->no_pesanan }}</td>
                                <td>{{ $order->pelanggan->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $st = strtolower($order->status ?? '');
                                        $stCls = 'is-info';
                                        if (in_array($st, ['pending','menunggu','belum bayar'])) $stCls = 'is-warn';
                                        elseif (in_array($st, ['selesai','lunas','sukses'])) $stCls = 'is-success';
                                        elseif (in_array($st, ['batal','gagal','ditolak'])) $stCls = 'is-danger';
                                    @endphp
                                    <span class="k-badge {{ $stCls }}">{{ $order->status }}</span>
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

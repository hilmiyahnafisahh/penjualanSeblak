@php
    $statusLabels = [
        'belum_bayar' => 'Belum Bayar',
        'lunas'       => 'Lunas',
        'semua'       => 'Semua',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran — Seblak Sangkuriang</title>
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
        <a href="{{ route('kasir.pembayaran') }}" class="k-nav-link active"><i class="bi bi-credit-card-2-front"></i> Pembayaran</a>
        <a href="{{ route('kasir.stok_menu') }}" class="k-nav-link"><i class="bi bi-box-seam"></i> Stok &amp; Menu</a>
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
                <h1>Pembayaran</h1>
                <p class="subtitle">Konfirmasi dan lihat status pembayaran.</p>
            </div>
        </div>

        <div class="k-filter-bar">
            @foreach($statusLabels as $key => $label)
                <a href="{{ route('kasir.pembayaran', ['status' => $key]) }}"
                   class="k-filter-pill {{ $statusParam === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if(session('success'))
            <div class="k-alert is-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="k-alert is-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        <div class="k-panel">
            <div class="k-panel-head">
                <div>
                    <h2>Daftar Pembayaran</h2>
                    <p class="panel-sub">{{ $pembayaran->count() }} data ditemukan.</p>
                </div>
            </div>

            @if($pembayaran->isEmpty())
                <div class="k-empty">Tidak ada data pembayaran.</div>
            @else
                <table class="k-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No Pembayaran</th>
                            <th>No Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembayaran as $item)
                            @php
                                $sp = strtolower($item->status_pembayaran ?? '');
                                $spCls = $sp === 'lunas' ? 'is-success' : 'is-warn';
                            @endphp
                            <tr>
                                <td style="color:var(--ink-400);font-weight:600;">{{ $loop->iteration }}</td>
                                <td style="font-weight:600;">{{ $item->id_pembayaran }}</td>
                                <td>{{ $item->pemesanan?->id_pesanan ?? '-' }}</td>
                                <td>{{ $item->pemesanan?->Pelanggan?->nama_pelanggan ?? '-' }}</td>
                                <td>{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
                                <td><span class="k-badge {{ $spCls }}">{{ ucfirst($item->status_pembayaran) }}</span></td>
                                <td style="font-size:0.82rem;color:var(--ink-500);">
                                    {{ optional($item->tanggal_pembayaran)->format('d M Y H:i')
                                       ?? optional($item->pemesanan?->tanggal_pemesanan)->format('d M Y H:i')
                                       ?? '-' }}
                                </td>
                                <td style="text-align:right;" class="k-num">
                                    Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}
                                </td>
                                <td style="text-align:center;">
                                    @if($item->status_pembayaran === 'pending' && strtolower($item->metode_pembayaran) === 'tunai')
                                        <form action="{{ route('kasir.pembayaran.bayar', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="k-btn-action">
                                                <i class="bi bi-cash-coin"></i> Bayar Tunai
                                            </button>
                                        </form>
                                    @else
                                        <span class="k-dash">—</span>
                                    @endif
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

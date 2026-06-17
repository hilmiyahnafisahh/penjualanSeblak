<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Masuk | Kasir</title>
  <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
        <div class="mb-4">
            <a href="{{ route('kasir.dashboard') }}" class="d-block p-3 rounded-3 mb-2">Dashboard</a>
            <a href="{{ route('kasir.pesanan') }}" class="d-block p-3 rounded-3 mb-2 active">Pesanan Masuk</a>
            <a href="{{ route('kasir.pembayaran') }}" class="d-block p-3 rounded-3 mb-2">Pembayaran</a>
            <a href="{{ route('kasir.stok_menu') }}" class="d-block p-3 rounded-3 mb-2">Stok & Menu</a>
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
        <h1>Pesanan Masuk</h1>
        <p>Kelola pesanan berdasarkan status.</p>
      </div>
      <div class="k-timestamp">
        <span class="k-status-dot"></span>
        <span>{{ now()->translatedFormat('l, d M Y — H:i') }}</span>
      </div>
    </div>

    <div class="k-filter-bar">
      @foreach($statusLabels as $key => $label)
        <a href="{{ route('kasir.pesanan', ['status' => $key]) }}"
           class="k-filter-pill {{ ($status ?? 'pending') === $key ? 'active' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="k-alert is-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="k-alert is-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        <div class="k-panel">
            <div class="k-panel-head">
                <div>
                    <h2>Daftar Pesanan</h2>
                    <p class="panel-sub">{{ $pesanan->count() }} pesanan ditemukan.</p>
                </div>
            </div>

            @if($pesanan->isEmpty())
                <div class="k-empty">Tidak ada pesanan dengan status ini.</div>
            @else
                <table class="k-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>No Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesanan as $order)
                            @php
                                $st = strtolower($order->status_pemesanan ?? '');
                                $stCls = match($st) {
                                    'belumdibayar' => 'is-warn',
                                    'diproses'     => 'is-info',
                                    'selesai'      => 'is-success',
                                    default        => 'is-info',
                                };
                                $stLabel = match($st) {
                                    'belumdibayar' => 'Belum Dibayar',
                                    default => ucfirst($order->status_pemesanan),
                                };
                                $bisa_selesaikan =
                                    $st === 'diproses'
                                    && $order->pembayaran
                                    && strtolower($order->pembayaran->metode_pembayaran) === 'qris'
                                    && in_array(strtolower($order->pembayaran->status_pembayaran), ['lunas','settlement','capture']);
                            @endphp
                            <tr>
                                <td style="color:var(--ink-400);font-weight:600;">{{ $loop->iteration }}</td>
                                <td style="font-weight:600;color:var(--ink-900);">{{ $order->id_pesanan }}</td>
                                <td>{{ $order->Pelanggan?->nama_pelanggan ?? '-' }}</td>
                                <td><span class="k-badge {{ $stCls }}">{{ $stLabel }}</span></td>
                                <td style="font-size:0.82rem;color:var(--ink-500);">
                                    {{ optional($order->tanggal_pemesanan)->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td style="text-align:right;" class="k-num">
                                    Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                                </td>
                                <td style="text-align:center;">
                                    @if($bisa_selesaikan)
                                        <form action="{{ route('kasir.pesanan.selesaikan', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="k-btn-action">
                                                <i class="bi bi-check2-circle"></i> Selesaikan
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

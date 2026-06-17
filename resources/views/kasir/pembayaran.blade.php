<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran | Kasir</title>
  <link rel="stylesheet" href="{{ asset('css/seblak-kasir.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
@php
  $statusLabels = [
    'belum_bayar' => 'Belum Bayar',
    'lunas'       => 'Lunas',
    'semua'       => 'Semua',
  ];
  $payBadge = [
    'Belum Bayar' => 'is-danger',
    'Pending'     => 'is-warn',
    'Lunas'       => 'is-success',
    'Berhasil'    => 'is-success',
    'Gagal'       => 'is-danger',
  ];
@endphp

<div class="kasir-shell">
  <aside class="kasir-sidebar">
    <div class="k-brand">
      <img src="{{ asset('logo_seblak.png') }}" alt="Seblak">
      <div class="k-brand-text">
        <div class="k-name">Seblak Sangkuriang</div>
        <div class="k-sub">Panel Kasir</div>
      </div>
    </div>

    <div class="k-nav-label">Operasional</div>
    <nav>
      <a href="{{ route('kasir.dashboard') }}" class="k-nav-link">
        <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
      </a>
      <a href="{{ route('kasir.pesanan') }}" class="k-nav-link">
        <i class="bi bi-receipt"></i><span>Pesanan Masuk</span>
      </a>
      <a href="{{ route('kasir.pembayaran') }}" class="k-nav-link active">
        <i class="bi bi-credit-card-2-back"></i><span>Pembayaran</span>
      </a>
      <a href="{{ route('kasir.stok_menu') }}" class="k-nav-link">
        <i class="bi bi-box-seam"></i><span>Stok & Menu</span>
      </a>
    </nav>

    <div class="k-sidebar-footer">
      <div class="k-user-card">
        <div class="k-user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'K', 0, 1)) }}</div>
        <div class="k-user-meta">
          <div class="role">Login sebagai</div>
          <div class="name">{{ Auth::user()->name ?? 'Kasir' }}</div>
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
        <h1>Konfirmasi Pembayaran</h1>
        <p>Lihat status pembayaran pesanan dan konfirmasi transaksi tunai.</p>
      </div>
      <div class="k-timestamp">
        <span class="k-status-dot"></span>
        <span>{{ now()->translatedFormat('l, d M Y — H:i') }}</span>
      </div>
    </div>

    @if(session('success'))
      <div class="k-alert is-success">
        <i class="bi bi-check-circle-fill"></i>
        <div>{{ session('success') }}</div>
      </div>
    @endif
    @if(session('error'))
      <div class="k-alert is-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div>{{ session('error') }}</div>
      </div>
    @endif

    <div class="k-filter-bar">
      @foreach($statusLabels as $key => $label)
        <a href="{{ route('kasir.pembayaran', ['status' => $key]) }}"
           class="k-filter-pill {{ ($status ?? 'belum_bayar') === $key ? 'active' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    <section class="k-panel">
      <div class="k-panel-head">
        <h2>Daftar Pembayaran</h2>
        <span class="k-badge">{{ $pembayaran->count() }} data</span>
      </div>

      @if($pembayaran->isEmpty())
        <div class="k-empty">
          <i class="bi bi-wallet2" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
          Tidak ada data pembayaran.
        </div>
      @else
        <div class="k-table-wrap">
          <table class="k-table">
            <thead>
              <tr>
                <th style="width:50px;">No</th>
                <th>No Pembayaran</th>
                <th>No Pesanan</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th>Aksi</th>
                <th style="text-align:right;">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pembayaran as $i => $item)
                @php
                  $payStatus = ucwords(str_replace('_',' ',$item->status_pembayaran ?? 'pending'));
                  $cls = $payBadge[$payStatus] ?? 'is-info';
                @endphp
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td><span class="k-link">{{ $item->no_pembayaran ?? '-' }}</span></td>
                  <td>{{ $item->pesanan->no_pesanan ?? '-' }}</td>
                  <td>{{ $item->pesanan->pelanggan->name ?? '-' }}</td>
                  <td><span class="k-badge {{ $cls }}">{{ $payStatus }}</span></td>
                  <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }}</td>
                  <td>{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
                  <td>
                    @if(($item->status_pembayaran ?? '') === 'pending' && strtolower($item->metode_pembayaran ?? '') === 'tunai')
                      <form action="{{ route('kasir.pembayaran.bayarTunai', $item->id_pembayaran) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="k-btn-action"><i class="bi bi-cash-coin"></i> Bayar Tunai</button>
                      </form>
                    @else
                      <span class="k-dash">—</span>
                    @endif
                  </td>
                  <td class="k-num" style="text-align:right;">Rp {{ number_format($item->total_pembayaran ?? 0, 0, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </section>
  </main>
</div>

</body>
</html>

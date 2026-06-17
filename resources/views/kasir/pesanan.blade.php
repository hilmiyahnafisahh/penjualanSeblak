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
@php
  $statusLabels = [
    'pending'  => 'Pending',
    'diproses' => 'Diproses',
    'selesai'  => 'Selesai',
    'semua'    => 'Semua',
  ];
  $statusBadge = [
    'Pending'      => 'is-warn',
    'Diproses'     => 'is-info',
    'Selesai'      => 'is-success',
    'Belumdibayar' => 'is-danger',
    'Belum Bayar'  => 'is-danger',
    'Lunas'        => 'is-success',
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
      <a href="{{ route('kasir.pesanan') }}" class="k-nav-link active">
        <i class="bi bi-receipt"></i><span>Pesanan Masuk</span>
      </a>
      <a href="{{ route('kasir.pembayaran') }}" class="k-nav-link">
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

    <section class="k-panel">
      <div class="k-panel-head">
        <h2>Daftar Pesanan</h2>
        <span class="k-badge">{{ $pesanan->count() }} pesanan</span>
      </div>

      @if($pesanan->isEmpty())
        <div class="k-empty">
          <i class="bi bi-inbox" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
          Tidak ada pesanan dengan status ini.
        </div>
      @else
        <div class="k-table-wrap">
          <table class="k-table">
            <thead>
              <tr>
                <th style="width:50px;">No</th>
                <th>No Pesanan</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th style="text-align:right;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pesanan as $i => $order)
                @php
                  $statusText = ucfirst(str_replace('_',' ',$order->status_pesanan ?? 'pending'));
                  $badgeClass = $statusBadge[$statusText] ?? 'is-info';
                @endphp
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td><span class="k-link">{{ $order->no_pesanan }}</span></td>
                  <td>{{ $order->pelanggan->name ?? '-' }}</td>
                  <td><span class="k-badge {{ $badgeClass }}">{{ $statusText }}</span></td>
                  <td>{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y H:i') }}</td>
                  <td class="k-num" style="text-align:right;">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</td>
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

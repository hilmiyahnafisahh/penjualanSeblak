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
<div class="d-flex">
    <aside class="sidebar p-4 flex-shrink-0" style="width:280px;">
        <div class="mb-5 d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo Seblak" style="width:42px;height:42px;object-fit:contain;background:#fff;border-radius:50%;padding:2px;">
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

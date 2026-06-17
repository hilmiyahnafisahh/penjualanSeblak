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

        <div class="card shadow-sm p-4 table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Daftar Pesanan</h2>
                <span class="badge bg-secondary">{{ $pesanan->count() }} pesanan</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            @if($pesanan->isEmpty())
                <div class="text-center py-5 text-muted">
                    Tidak ada pesanan dengan status ini
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>No</th>
                                <th>No Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan as $order)
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
                                    <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->status_pemesanan === 'diproses' && $order->pembayaran && strtolower($order->pembayaran->metode_pembayaran) === 'qris' && in_array(strtolower($order->pembayaran->status_pembayaran), ['lunas','settlement','capture']))
                                            <form action="{{ route('kasir.pesanan.selesaikan', $order->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    ✓ Selesaikan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
</div>

</body>
</html>

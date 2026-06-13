@php
    $statusLabels = [
        'belumdibayar' => 'Belum Dibayar',
        'diproses'     => 'Diproses',
        'selesai'      => 'Selesai',
        'semua'        => 'Semua',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fdf0f0; }
        header { background: white; }
        .btn-merah { background: #8b1a1a; color: white; border: none; }
        .btn-merah:hover { background: #600e0e; color: white; }
        .badge-status { font-size: .85rem; }
        .table-card { border-radius: 1rem; }
        .nav-pill-custom .nav-link { color: #8b1a1a; border-radius: 2rem; }
        .nav-pill-custom .nav-link.active { background: #8b1a1a; color: white; }
    </style>
</head>
<body>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <defs>
    <symbol id="user" viewBox="0 0 24 24">
      <path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/>
    </symbol>
  </defs>
</svg>

<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header justify-content-center">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-seblak.png') }}" alt="Seblak Sangkuriang" style="width:80px;height:80px;object-fit:contain;border-radius:50%;" class="mb-1">
        <p class="text-muted small">Halo, {{ session('pelanggan_user_name', 'Pelanggan') }}!</p>
    </div>
    <div class="d-grid gap-2">
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
        <a href="{{ route('pelanggan.pesanan') }}" class="btn btn-merah">Pesanan Saya</a>
        <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-outline-secondary">Riwayat Pembayaran</a>
        <hr>
        <form action="{{ route('pelanggan.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger w-100">Keluar</button>
        </form>
    </div>
  </div>
</div>

<header>
  <div class="container-fluid">
    <div class="row py-3 border-bottom align-items-center">
      <div class="col-4 d-flex align-items-center gap-2">
        <img src="{{ asset('images/logo-seblak.png') }}" alt="Seblak Sangkuriang" style="height:44px;width:44px;object-fit:contain;border-radius:50%;">
        <div class="d-none d-sm-block" style="line-height:1.2;">
          <div style="font-size:.7rem;color:#888;letter-spacing:.05em;">Welcome to</div>
          <div style="font-size:.95rem;font-weight:700;color:#8b1a1a;">Seblak Sangkuriang</div>
        </div>
      </div>
      <div class="col-4 text-center d-none d-lg-block">
        <span class="text-muted">Panel Pelanggan</span>
      </div>
      <div class="col-8 col-lg-4 d-flex justify-content-end gap-3 align-items-center">
        <span class="text-muted d-none d-lg-block small">{{ session('pelanggan_user_name', 'Pelanggan') }}</span>
        <a href="#" class="rounded-circle bg-light p-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
        </a>
      </div>
    </div>
    <div class="row border-bottom">
      <div class="col-12 py-2">
        <ul class="nav nav-pills nav-pill-custom gap-2">
          <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link">Dashboard</a></li>
          <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link active">Pesanan Saya</a></li>
          <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link">Riwayat Pembayaran</a></li>
        </ul>
      </div>
    </div>
  </div>
</header>

<section class="py-5">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4>Pesanan Saya</h4>
        <p class="text-muted mb-0 small">Kelola dan pantau status pesananmu.</p>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Filter status --}}
    <div class="mb-4">
      @foreach($statusLabels as $key => $label)
        <a href="{{ route('pelanggan.pesanan', ['status' => $key]) }}"
           class="btn btn-sm {{ $statusParam === $key ? 'btn-merah' : 'btn-outline-secondary' }} me-2 mb-2">
          {{ $label }}
        </a>
      @endforeach
    </div>

    <div class="card shadow-sm p-4 table-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Pesanan</h5>
        <span class="badge bg-secondary">{{ $pesanan->count() }} pesanan</span>
      </div>

      @if($pesanan->isEmpty())
        <div class="text-center py-5 text-muted">
          Tidak ada pesanan dengan status ini.
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr class="text-muted small text-uppercase">
                <th>No</th>
                <th>No Pesanan</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pesanan as $order)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $order->id_pesanan }}</td>
                  <td>
                    <span class="badge bg-{{ $order->status_pemesanan === 'selesai' ? 'success' : ($order->status_pemesanan === 'diproses' ? 'info' : 'warning') }} text-dark badge-status">
                      {{ ucfirst($order->status_pemesanan) }}
                    </span>
                  </td>
                  <td>{{ optional($order->tanggal_pemesanan)->format('d M Y H:i') }}</td>
                  <td>Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
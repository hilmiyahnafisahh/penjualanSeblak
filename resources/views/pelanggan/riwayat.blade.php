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
    <title>Riwayat Pembayaran | Seblak</title>
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
        <a href="{{ route('pelanggan.pesanan') }}" class="btn btn-outline-secondary">Pesanan Saya</a>
        <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-merah">Riwayat Pembayaran</a>
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
          <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link">Pesanan Saya</a></li>
          <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link active">Riwayat Pembayaran</a></li>
        </ul>
      </div>
    </div>
  </div>
</header>

<section class="py-5">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4>Riwayat Pembayaran</h4>
        <p class="text-muted mb-0 small">Semua riwayat transaksi pembayaranmu.</p>
      </div>
    </div>

    {{-- Filter status --}}
    <div class="mb-4">
      @foreach($statusLabels as $key => $label)
        <a href="{{ route('pelanggan.riwayat', ['status' => $key]) }}"
           class="btn btn-sm {{ $statusParam === $key ? 'btn-merah' : 'btn-outline-secondary' }} me-2 mb-2">
          {{ $label }}
        </a>
      @endforeach
    </div>

    {{-- List bergaya riwayat.blade referensi --}}
    @if($pembayaran->isEmpty())
      <div class="text-center py-5 text-muted">
        Tidak ada data pembayaran.
      </div>
    @else
      <ul class="list-group mb-3">
        @php $totalAll = 0; @endphp
        @foreach($pembayaran as $item)
          @php $totalAll += $item->total_pembayaran; @endphp
          <li class="list-group-item d-flex justify-content-between align-items-start py-3">
            <div>
              <h6 class="my-0 fw-bold">{{ $item->id_pembayaran }}</h6>
              <small class="text-muted">
                No Pesanan: {{ $item->pemesanan?->id_pesanan ?? '-' }} &nbsp;|&nbsp;
                {{ optional($item->tanggal_pembayaran)->format('d M Y H:i') }}
              </small><br>
              <span class="badge bg-{{ $item->status_pembayaran === 'lunas' ? 'success' : 'warning' }} text-dark mt-1 badge-status">
                {{ ucfirst($item->status_pembayaran) }}
              </span>
            </div>
            <span class="fw-bold">Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}</span>
          </li>
        @endforeach
        <li class="list-group-item d-flex justify-content-between bg-light">
          <div class="text-success">
            <h6 class="my-0">Total Semua Transaksi</h6>
          </div>
          <span><strong>Rp {{ number_format($totalAll, 0, ',', '.') }}</strong></span>
        </li>
      </ul>
    @endif

  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
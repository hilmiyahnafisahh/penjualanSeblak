@php
$statusLabels = ['semua'=>'Semua','belumdibayar'=>'Belum Bayar','diproses'=>'Diproses','selesai'=>'Selesai'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan | Seblak Sangkuriang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{--merah:#8b1a1a;--merah-gelap:#600e0e;--bg:#fdf0f0;}
        body{background:var(--bg);font-family:'Segoe UI',sans-serif;}
        .navbar-custom{background:white;box-shadow:0 2px 12px rgba(0,0,0,.08);position:sticky;top:0;z-index:200;}
        .btn-merah{background:var(--merah);color:white;border:none;}
        .btn-merah:hover{background:var(--merah-gelap);color:white;}
        .nav-tabs-custom{border-bottom:2px solid #f0d0d0;}
        .nav-tabs-custom .nav-link{color:#555;border:none;padding:.6rem 1.2rem;font-size:.875rem;border-radius:0;}
        .nav-tabs-custom .nav-link.active{color:var(--merah);font-weight:700;border-bottom:2px solid var(--merah);margin-bottom:-2px;}
        .filter-pill{border-radius:2rem;font-size:.8rem;padding:.35rem 1rem;}

        /* Timeline card */
        .order-card{background:white;border-radius:1.1rem;border:1px solid #f0d0d0;margin-bottom:1.25rem;overflow:hidden;}
        .order-header{padding:.9rem 1.25rem;border-bottom:1px solid #fce8e8;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;}
        .order-body{padding:1rem 1.25rem;}
        .order-footer{padding:.75rem 1.25rem;border-top:1px solid #fce8e8;}

        /* Badges */
        .pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:2rem;padding:.28rem .85rem;font-size:.73rem;font-weight:600;}
        .pill-kuning{background:#fff3cd;color:#856404;}
        .pill-biru{background:#cff4fc;color:#055160;}
        .pill-hijau{background:#d1e7dd;color:#0f5132;}
        .pill-abu{background:#e9ecef;color:#495057;}
        .pill-merah{background:#f8d7da;color:#842029;}

        /* Item rows */
        .item-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.5rem 0;border-bottom:1px dashed #f0d0d0;}
        .item-row:last-child{border-bottom:none;}
        .item-name{font-weight:600;font-size:.875rem;}
        .item-sub{font-size:.73rem;color:#aaa;margin-top:.15rem;}
        .item-price{font-weight:700;font-size:.875rem;color:var(--merah);white-space:nowrap;margin-left:1rem;}

        /* Summary box */
        .summary-box{background:white;border-radius:1rem;border:1.5px solid #f0d0d0;padding:1.25rem;}
        .summary-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f9f0f0;font-size:.875rem;}
        .summary-row:last-child{border-bottom:none;}

        /* Pay status indicator */
        .pay-status-bar{display:flex;align-items:center;gap:.6rem;padding:.65rem 1rem;border-radius:.75rem;font-size:.8rem;font-weight:600;}
        .pay-status-lunas{background:#d1e7dd;color:#0f5132;}
        .pay-status-pending{background:#fff3cd;color:#856404;}
        .pay-status-batal{background:#f8d7da;color:#842029;}
    </style>
</head>
<body>
<svg style="display:none;"><defs>
  <symbol id="ic-user" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/></symbol>
</defs></svg>

{{-- Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <img src="{{ asset('images/logo-seblak.png') }}" style="height:36px;width:36px;border-radius:50%;object-fit:contain;">
      <div style="font-weight:700;font-size:.9rem;color:var(--merah);">Seblak Sangkuriang</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="list-group list-group-flush">
      <a href="{{ route('pelanggan.dashboard') }}" class="list-group-item list-group-item-action py-3 px-4"><i class="bi bi-house-door me-2" style="color:var(--merah)"></i>Beranda</a>
      <a href="{{ route('pelanggan.keranjang') }}" class="list-group-item list-group-item-action py-3 px-4"><i class="bi bi-cart3 me-2" style="color:var(--merah)"></i>Keranjang</a>
      <a href="{{ route('pelanggan.pesanan') }}" class="list-group-item list-group-item-action py-3 px-4"><i class="bi bi-bag-check me-2" style="color:var(--merah)"></i>Pesanan Saya</a>
      <a href="{{ route('pelanggan.riwayat') }}" class="list-group-item list-group-item-action py-3 px-4 fw-bold" style="color:var(--merah)"><i class="bi bi-clock-history me-2"></i>Riwayat</a>
    </nav>
    <div class="p-4"><form action="{{ route('pelanggan.logout') }}" method="POST">@csrf<button class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button></form></div>
  </div>
</div>

{{-- Navbar --}}
<nav class="navbar-custom">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center py-2 gap-3">
      <a href="{{ route('pelanggan.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="{{ asset('images/logo-seblak.png') }}" style="height:40px;width:40px;border-radius:50%;object-fit:contain;">
        <div class="d-none d-sm-block" style="font-weight:700;color:var(--merah);font-size:.95rem;">Seblak Sangkuriang</div>
      </a>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex align-items-center gap-1"><i class="bi bi-cart3"></i>Keranjang</a>
        <span class="d-none d-lg-block text-muted" style="font-size:.82rem;">{{ session('pelanggan_user_name','') }}</span>
        <button class="btn btn-light rounded-circle p-1" style="width:38px;height:38px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="18" height="18"><use xlink:href="#ic-user"></use></svg>
        </button>
      </div>
    </div>
    <ul class="nav nav-tabs-custom">
      <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link">Beranda</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link">Pesanan Saya</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link active">Riwayat</a></li>
    </ul>
  </div>
</nav>

<div class="container-fluid px-3 py-4" style="max-width:960px;">
  <div class="d-flex justify-content-between align-items-center mb-1">
    <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:var(--merah)"></i>Riwayat Pemesanan</h4>
  </div>
  <p class="text-muted small mb-4">Semua pesanan yang pernah kamu buat beserta detail pembayarannya.</p>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Filter --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    @foreach($statusLabels as $key => $label)
      <a href="{{ route('pelanggan.riwayat',['status'=>$key]) }}"
         class="btn filter-pill {{ $statusParam===$key ? 'btn-merah' : 'btn-outline-secondary' }}">
        @if($key==='semua') 📋 @elseif($key==='belumdibayar') ⏳ @elseif($key==='diproses') 🔄 @else ✅ @endif
        {{ $label }}
      </a>
    @endforeach
  </div>

  @if($pesanan->isEmpty())
    <div class="text-center py-5 bg-white rounded-3 border" style="border-color:#f0d0d0!important;">
      <div style="font-size:3.5rem;">📋</div>
      <h6 class="mt-3 fw-bold">Belum ada riwayat pesanan</h6>
      <p class="text-muted small">Yuk mulai pesan menu favoritmu!</p>
      <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah btn-sm mt-1">Lihat Menu</a>
    </div>
  @else
  <div class="row g-4">
    {{-- Kiri: Daftar pesanan --}}
    <div class="col-lg-8">
      @php $grandTotal = 0; $totalLunas = 0; $totalPending = 0; @endphp
      @foreach($pesanan as $order)
      @php
        $bayar    = $order->pembayaran;
        $metode   = $bayar ? strtolower($bayar->metode_pembayaran) : null;
        $stsBayar = $bayar ? strtolower($bayar->status_pembayaran) : 'belum';
        $sudahBayar = in_array($stsBayar, ['lunas','settlement','capture']);
        $itemTotal = $order->DetailPesanan->sum('subtotal');
        $grandTotal += $itemTotal;
        if($sudahBayar) $totalLunas += $itemTotal; else $totalPending += $itemTotal;
      @endphp
      <div class="order-card">
        {{-- Header --}}
        <div class="order-header">
          <div>
            <div class="fw-bold" style="color:var(--merah);font-size:.9rem;">{{ $order->id_pesanan }}</div>
            <div class="text-muted" style="font-size:.73rem;margin-top:.2rem;">
              <i class="bi bi-calendar3 me-1"></i>{{ optional($order->tanggal_pemesanan)->format('d M Y, H:i') }}
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            {{-- Metode --}}
            @if($metode === 'qris')
              <span class="pill pill-abu"><i class="bi bi-qr-code me-1"></i>QRIS</span>
            @elseif($metode === 'tunai')
              <span class="pill pill-abu"><i class="bi bi-cash me-1"></i>Tunai</span>
            @endif
            {{-- Status pesanan --}}
            @if($order->status_pemesanan === 'belumdibayar')
              <span class="pill pill-kuning"><i class="bi bi-clock me-1"></i>Menunggu Bayar</span>
            @elseif($order->status_pemesanan === 'diproses')
              <span class="pill pill-biru"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
            @elseif($order->status_pemesanan === 'selesai')
              <span class="pill pill-hijau"><i class="bi bi-check-circle me-1"></i>Selesai</span>
            @endif
            {{-- Status bayar --}}
            @if($sudahBayar)
              <span class="pill pill-hijau"><i class="bi bi-patch-check-fill me-1"></i>Lunas</span>
            @else
              <span class="pill pill-merah"><i class="bi bi-exclamation-triangle me-1"></i>Belum Bayar</span>
            @endif
          </div>
        </div>

        {{-- Body: items --}}
        <div class="order-body">
          @if($order->DetailPesanan->isNotEmpty())
            @foreach($order->DetailPesanan as $detail)
            <div class="item-row">
              <div class="flex-grow-1">
                <div class="item-name">{{ $detail->menu->nama_menu ?? '-' }} <span class="text-muted fw-normal">× {{ $detail->jumlah }}</span></div>
                @if(!empty($detail->topping))
                  <div class="item-sub"><i class="bi bi-plus-circle me-1"></i>{{ collect($detail->topping)->pluck('nama_barang')->join(', ') }}</div>
                @endif
                @if(!empty($detail->catatan))
                  <div class="item-sub"><i class="bi bi-pencil me-1"></i>{{ $detail->catatan }}</div>
                @endif
              </div>
              <div class="item-price">Rp {{ number_format($detail->subtotal,0,',','.') }}</div>
            </div>
            @endforeach
          @endif

          <div class="d-flex justify-content-between align-items-center pt-3">
            <span class="text-muted small">Total Pesanan</span>
            <span class="fw-bold" style="color:var(--merah);font-size:1rem;">Rp {{ number_format($itemTotal,0,',','.') }}</span>
          </div>
        </div>

        {{-- Footer: status pembayaran detail --}}
        <div class="order-footer">
          @if($sudahBayar)
            <div class="pay-status-bar pay-status-lunas">
              <i class="bi bi-patch-check-fill fs-5"></i>
              <div>
                <div>Pembayaran Lunas</div>
                @if($bayar && $bayar->tanggal_pembayaran)
                  <div style="font-weight:400;font-size:.72rem;">{{ optional($bayar->tanggal_pembayaran)->format('d M Y, H:i') }}</div>
                @endif
              </div>
            </div>
          @elseif($stsBayar === 'batal' || $stsBayar === 'expire' || $stsBayar === 'deny')
            <div class="pay-status-bar pay-status-batal">
              <i class="bi bi-x-circle-fill fs-5"></i>
              <div>Pembayaran Dibatalkan / Kadaluarsa</div>
            </div>
          @else
            @if($metode === 'tunai')
            <div class="pay-status-bar pay-status-pending">
              <i class="bi bi-cash fs-5"></i>
              <div>
                <div>Bayar Tunai di Kasir</div>
                <div style="font-weight:400;font-size:.72rem;">Tunjukkan kode <strong>{{ $order->id_pesanan }}</strong> ke kasir</div>
              </div>
            </div>
            @elseif($metode === 'qris')
            <div class="pay-status-bar pay-status-pending">
              <i class="bi bi-qr-code fs-5"></i>
              <div>
                <div>Menunggu Konfirmasi QRIS</div>
                <div style="font-weight:400;font-size:.72rem;">Jika sudah scan, tunggu konfirmasi otomatis</div>
              </div>
            </div>
            @else
            <div class="pay-status-bar pay-status-pending">
              <i class="bi bi-clock fs-5"></i>
              <div>Menunggu Pembayaran</div>
            </div>
            @endif
          @endif
        </div>
      </div>
      @endforeach
    </div>

    {{-- Kanan: Ringkasan total --}}
    <div class="col-lg-4">
      <div class="summary-box" style="position:sticky;top:80px;">
        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2" style="color:var(--merah)"></i>Ringkasan</h6>
        <div class="summary-row">
          <span class="text-muted">Total Pesanan</span>
          <span class="fw-bold">{{ $pesanan->count() }} pesanan</span>
        </div>
        <div class="summary-row">
          <span class="text-muted">Total Nilai</span>
          <span class="fw-bold">Rp {{ number_format($grandTotal,0,',','.') }}</span>
        </div>
        <div class="summary-row">
          <span class="text-muted">Sudah Lunas</span>
          <span class="fw-bold" style="color:#0f5132;">Rp {{ number_format($totalLunas,0,',','.') }}</span>
        </div>
        <div class="summary-row">
          <span class="text-muted">Belum Bayar</span>
          <span class="fw-bold" style="color:#842029;">Rp {{ number_format($totalPending,0,',','.') }}</span>
        </div>
        <hr style="border-color:#f0d0d0;">
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">Total Lunas</span>
          <span class="fw-bold fs-5" style="color:var(--merah);">Rp {{ number_format($totalLunas,0,',','.') }}</span>
        </div>
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah w-100 mt-3 btn-sm">
          <i class="bi bi-plus-lg me-1"></i>Pesan Lagi
        </a>
      </div>
    </div>
  </div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

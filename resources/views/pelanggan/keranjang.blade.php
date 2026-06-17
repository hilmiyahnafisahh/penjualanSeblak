<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang | Seblak Sangkuriang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
</head>
<body>
<svg style="display:none;">
  <defs><symbol id="ic-user" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/></symbol></defs>
</svg>

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
      <a href="{{ route('pelanggan.keranjang') }}" class="list-group-item list-group-item-action py-3 px-4 fw-bold" style="color:var(--merah)"><i class="bi bi-cart3 me-2"></i>Keranjang</a>
      <a href="{{ route('pelanggan.pesanan') }}" class="list-group-item list-group-item-action py-3 px-4"><i class="bi bi-bag-check me-2" style="color:var(--merah)"></i>Pesanan Saya</a>
      <a href="{{ route('pelanggan.riwayat') }}" class="list-group-item list-group-item-action py-3 px-4"><i class="bi bi-clock-history me-2" style="color:var(--merah)"></i>Riwayat</a>
    </nav>
    <div class="p-4"><form action="{{ route('pelanggan.logout') }}" method="POST">@csrf<button class="btn btn-outline-danger w-100">Keluar</button></form></div>
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
        <span class="d-none d-md-block text-muted" style="font-size:.82rem;">{{ session('pelanggan_user_name','') }}</span>
        <button class="btn btn-light rounded-circle p-1" style="width:38px;height:38px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="18" height="18"><use xlink:href="#ic-user"></use></svg>
        </button>
      </div>
    </div>
    <ul class="nav nav-tabs-custom">
      <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link">Beranda</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link">Pesanan Saya</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link">Riwayat</a></li>
    </ul>
  </div>
</nav>

{{-- Content --}}
<div class="container-fluid px-3 py-4" style="max-width:1000px;">

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show rounded-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  <h4 class="fw-bold mb-4"><i class="bi bi-cart3 me-2" style="color:var(--merah)"></i>Keranjang Anda</h4>

  @if(empty($keranjang))
    <div class="text-center py-5">
      <div style="font-size:4rem;">🛒</div>
      <h5 class="mt-3 text-muted">Keranjang masih kosong</h5>
      <p class="text-muted small">Yuk tambahkan menu favoritmu!</p>
      <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah">Lihat Menu</a>
    </div>
  @else
  <div class="row g-4">
    <div class="col-lg-7">
      @foreach($keranjang as $itemKey => $item)
      <div class="cart-item-card">
        <div class="cart-item-header d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold" style="font-size:.95rem;">{{ $item['nama'] }}</div>
            <div class="text-muted" style="font-size:.8rem;">Rp {{ number_format($item['harga'],0,',','.') }} / porsi</div>
          </div>
          <form action="{{ route('pelanggan.keranjang.remove') }}" method="POST" class="d-inline">
            @csrf <input type="hidden" name="item_key" value="{{ $itemKey }}">
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" title="Hapus" style="width:28px;height:28px;"><i class="bi bi-trash" style="font-size:.7rem;"></i></button>
          </form>
        </div>
        <div class="cart-item-body">
          {{-- Rincian --}}
          @if(!empty($item['rasa'])||!empty($item['sayur_sawi'])||!empty($item['level_pedas']))
          <div class="mb-2">
            @if(!empty($item['rasa']))<span class="badge-rincian">🌶 {{ $item['rasa'] }}</span>@endif
            @if(!empty($item['sayur_sawi']))<span class="badge-rincian">🥬 {{ $item['sayur_sawi'] }}</span>@endif
            @if(!empty($item['level_pedas']))<span class="badge-rincian">🔥 {{ $item['level_pedas'] }}</span>@endif
            @if(!empty($item['catatan']))<span class="badge-rincian">📝 {{ $item['catatan'] }}</span>@endif
          </div>
          @endif
          {{-- Topping --}}
          @if(!empty($item['toppings']))
          <div class="mb-2">
            @foreach($item['toppings'] as $top)
              <span class="badge-rincian">+ {{ $top['nama_barang'] }} ×{{ $top['qty'] }}</span>
            @endforeach
          </div>
          @endif
          {{-- Qty + subtotal --}}
          <div class="d-flex justify-content-between align-items-center mt-2">
            <div class="qty-wrap">
              <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                @csrf<input type="hidden" name="item_key" value="{{ $itemKey }}"><input type="hidden" name="action" value="menu_decrease">
                <button type="submit" class="qty-btn">−</button>
              </form>
              <span class="fw-bold px-1">{{ $item['qty'] }}</span>
              <form action="{{ route('pelanggan.keranjang.update') }}" method="POST" class="d-inline">
                @csrf<input type="hidden" name="item_key" value="{{ $itemKey }}"><input type="hidden" name="action" value="menu_increase">
                <button type="submit" class="qty-btn">+</button>
              </form>
            </div>
            <div class="fw-bold" style="color:var(--merah);font-size:.95rem;">Rp {{ number_format($item['subtotal'],0,',','.') }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <div class="col-lg-5">
      <div class="summary-card">
        <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
        <div class="d-flex justify-content-between mb-1 small"><span>Subtotal ({{ collect($keranjang)->sum('qty') }} item)</span><span>Rp {{ number_format($total,0,',','.') }}</span></div>
        <hr>
        <div class="d-flex justify-content-between fw-bold mb-3"><span>Total</span><span style="color:var(--merah);font-size:1.1rem;">Rp {{ number_format($total,0,',','.') }}</span></div>

        <form action="{{ route('pelanggan.checkout.post') }}" method="POST" id="checkoutForm">
          @csrf
          <div class="mb-3">
            <div class="fw-semibold mb-2" style="font-size:.875rem;">Pilih Metode Pembayaran</div>
            <div class="d-flex gap-2">
              <input type="radio" class="d-none" name="metode_pembayaran" id="m_qris" value="qris" required>
              <label class="metode-btn flex-fill" for="m_qris" onclick="pilihMetode('qris')">
                <div style="font-size:1.5rem;">📱</div>
                <div class="fw-bold" style="font-size:.82rem;">QRIS</div>
                <div class="text-muted" style="font-size:.7rem;">Bayar Pakai QR</div>
              </label>
              <input type="radio" class="d-none" name="metode_pembayaran" id="m_tunai" value="tunai">
              <label class="metode-btn flex-fill" for="m_tunai" onclick="pilihMetode('tunai')">
                <div style="font-size:1.5rem;">💵</div>
                <div class="fw-bold" style="font-size:.82rem;">Tunai</div>
                <div class="text-muted" style="font-size:.7rem;">Bayar di Kasir</div>
              </label>
            </div>
            @error('metode_pembayaran')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>
          <div id="infoMetode" class="mb-3" style="display:none;"></div>
          <button type="submit" class="btn btn-merah w-100 py-2 fw-bold" id="btnCheckout" disabled>
            Lanjutkan Pembayaran
          </button>
        </form>
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-outline-secondary w-100 mt-2 btn-sm">+ Tambah Menu Lain</a>
      </div>
    </div>
  </div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function pilihMetode(m) {
  document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('selected'));
  document.querySelector('label[for="m_'+m+'"]').classList.add('selected');
  const info = document.getElementById('infoMetode');
  const btn  = document.getElementById('btnCheckout');
  btn.disabled = false;
  if (m === 'qris') {
    info.style.display = 'block';
    info.innerHTML = '<div class="p-2 rounded-3 small" style="background:#e8f4fd;color:#055160;"><i class="bi bi-qr-code me-1"></i><strong>QRIS:</strong> Setelah klik lanjutkan, scan QR code untuk menyelesaikan pembayaran.</div>';
    btn.textContent = 'Bayar Sekarang via QRIS';
  } else {
    info.style.display = 'block';
    info.innerHTML = '<div class="p-2 rounded-3 small" style="background:#fff3cd;color:#856404;"><i class="bi bi-cash me-1"></i><strong>Tunai:</strong> Tunjukkan nomor pesananmu ke kasir untuk menyelesaikan pembayaran.</div>';
    btn.textContent = 'Buat Pesanan & Bayar di Kasir';
  }
}
</script>
</body>
</html>

@php
$statusLabels = ['semua'=>'Semua','belumdibayar'=>'Belum Bayar','diproses'=>'Diproses','selesai'=>'Selesai'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya | Seblak Sangkuriang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
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

        /* Card */
        .order-card{background:white;border-radius:1.1rem;border:1px solid #f0d0d0;margin-bottom:1.25rem;overflow:hidden;}
        .order-header{padding:.9rem 1.25rem;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;cursor:pointer;transition:background .15s;}
        .order-header:hover{background:#fff8f8;}
        .order-body{padding:1rem 1.25rem;border-top:1px solid #fce8e8;display:none;}
        .order-body.open{display:block;}
        .order-footer{padding:.75rem 1.25rem;border-top:1px solid #fce8e8;background:#fff8f8;}
        .chevron{transition:transform .2s;flex-shrink:0;margin-left:.5rem;color:#aaa;}
        .order-header.expanded .chevron{transform:rotate(180deg);}

        /* Badges */
        .pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:2rem;padding:.28rem .85rem;font-size:.73rem;font-weight:600;}
        .pill-kuning{background:#fff3cd;color:#856404;}
        .pill-biru{background:#cff4fc;color:#055160;}
        .pill-hijau{background:#d1e7dd;color:#0f5132;}
        .pill-abu{background:#e9ecef;color:#495057;}
        .pill-merah{background:#f8d7da;color:#842029;}

        /* Item rows */
        .item-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.55rem 0;border-bottom:1px dashed #f0d0d0;}
        .item-row:last-child{border-bottom:none;}
        .item-name{font-weight:600;font-size:.875rem;}
        .item-sub{font-size:.73rem;color:#aaa;margin-top:.15rem;}
        .item-price{font-weight:700;font-size:.875rem;color:var(--merah);white-space:nowrap;margin-left:1rem;}

        /* Pay info box */
        .pay-box{border-radius:.85rem;padding:.85rem 1rem;display:flex;align-items:flex-start;gap:.75rem;}
        .pay-box-warning{background:#fff3cd;border:1px solid #ffe69c;}
        .pay-box-info{background:#e8f4fd;border:1px solid #bee5fd;}
        .pay-box-success{background:#d1e7dd;border:1px solid #a3cfbb;}
        .pay-icon{font-size:1.4rem;flex-shrink:0;}
        .pay-title{font-weight:700;font-size:.85rem;margin-bottom:.25rem;}
        .pay-desc{font-size:.78rem;color:#555;line-height:1.5;}

        /* Snap loading overlay */
        #snapOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;flex-direction:column;color:white;}
        #snapOverlay.show{display:flex;}
        .snap-spinner{width:46px;height:46px;border:4px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .8s linear infinite;margin-bottom:1rem;}
        @keyframes spin{to{transform:rotate(360deg)}}
    </style>
</head>
<body>
<svg style="display:none;"><defs>
  <symbol id="ic-user" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/></symbol>
</defs></svg>

<!-- Loading Snap overlay -->
<div id="snapOverlay">
  <div class="snap-spinner"></div>
  <div>Memuat halaman pembayaran...</div>
</div>

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
      <a href="{{ route('pelanggan.pesanan') }}" class="list-group-item list-group-item-action py-3 px-4 fw-bold" style="color:var(--merah)"><i class="bi bi-bag-check me-2"></i>Pesanan Saya</a>
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
        <a href="{{ route('pelanggan.keranjang') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex align-items-center gap-1"><i class="bi bi-cart3"></i> Keranjang</a>
        <span class="d-none d-lg-block text-muted" style="font-size:.82rem;">{{ session('pelanggan_user_name','') }}</span>
        <button class="btn btn-light rounded-circle p-1" style="width:38px;height:38px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
          <svg width="18" height="18"><use xlink:href="#ic-user"></use></svg>
        </button>
      </div>
    </div>
    <ul class="nav nav-tabs-custom">
      <li class="nav-item"><a href="{{ route('pelanggan.dashboard') }}" class="nav-link">Beranda</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.pesanan') }}" class="nav-link active">Pesanan Saya</a></li>
      <li class="nav-item"><a href="{{ route('pelanggan.riwayat') }}" class="nav-link">Riwayat</a></li>
    </ul>
  </div>
</nav>

<div class="container-fluid px-3 py-4" style="max-width:820px;">
  <div class="d-flex justify-content-between align-items-center mb-1">
    <h4 class="fw-bold mb-0"><i class="bi bi-bag-check me-2" style="color:var(--merah)"></i>Pesanan Saya</h4>
    <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah btn-sm"><i class="bi bi-plus-lg me-1"></i>Pesan Lagi</a>
  </div>
  <p class="text-muted small mb-3">Klik kartu pesanan untuk melihat detail. Tap sekali lagi untuk menutup.</p>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Filter --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    @foreach($statusLabels as $key => $label)
      <a href="{{ route('pelanggan.pesanan',['status'=>$key]) }}"
         class="btn filter-pill {{ $statusParam===$key ? 'btn-merah' : 'btn-outline-secondary' }}">
        @if($key==='semua')📋 @elseif($key==='belumdibayar')⏳ @elseif($key==='diproses')🔄 @else ✅@endif {{ $label }}
      </a>
    @endforeach
  </div>

  @if($pesanan->isEmpty())
    <div class="text-center py-5 bg-white rounded-3 border" style="border-color:#f0d0d0!important;">
      <div style="font-size:3.5rem;">🛍️</div>
      <h6 class="mt-3 fw-bold">Belum ada pesanan</h6>
      <p class="text-muted small">Yuk mulai pesan menu favoritmu!</p>
      <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-merah btn-sm mt-1">Lihat Menu</a>
    </div>
  @else
    @foreach($pesanan as $i => $order)
    @php
      $bayar      = $order->pembayaran;
      $metode     = $bayar ? strtolower($bayar->metode_pembayaran) : null;
      $stsBayar   = $bayar ? strtolower($bayar->status_pembayaran) : 'belum';
      $sudahBayar = in_array($stsBayar, ['lunas','settlement','capture']);
      $itemTotal  = $order->DetailPesanan->sum('subtotal');
    @endphp
    <div class="order-card" id="card-{{ $i }}">
      {{-- HEADER (clickable) --}}
      <div class="order-header" onclick="toggleDetail({{ $i }})">
        <div class="d-flex align-items-center gap-2 flex-wrap" style="min-width:0;">
          <i class="bi bi-chevron-down chevron" id="chev-{{ $i }}"></i>
          <div>
            <div class="fw-bold" style="color:var(--merah);font-size:.92rem;">{{ $order->id_pesanan }}</div>
            <div class="text-muted" style="font-size:.73rem;">
              <i class="bi bi-calendar3 me-1"></i>{{ optional($order->tanggal_pemesanan)->format('d M Y, H:i') }}
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end" onclick="event.stopPropagation()">
          @if($metode === 'qris')<span class="pill pill-abu"><i class="bi bi-qr-code me-1"></i>QRIS</span>
          @elseif($metode === 'tunai')<span class="pill pill-abu"><i class="bi bi-cash me-1"></i>Tunai</span>@endif

          @if($order->status_pemesanan === 'belumdibayar')<span class="pill pill-kuning"><i class="bi bi-clock me-1"></i>Menunggu Bayar</span>
          @elseif($order->status_pemesanan === 'diproses')<span class="pill pill-biru"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
          @elseif($order->status_pemesanan === 'selesai')<span class="pill pill-hijau"><i class="bi bi-check-circle me-1"></i>Selesai</span>@endif

          @if($sudahBayar)<span class="pill pill-hijau"><i class="bi bi-patch-check me-1"></i>Lunas</span>
          @else<span class="pill pill-merah"><i class="bi bi-exclamation-circle me-1"></i>Belum Bayar</span>@endif

          <div class="fw-bold ms-1" style="color:var(--merah);font-size:.9rem;">Rp {{ number_format($itemTotal,0,',','.') }}</div>
        </div>
      </div>

      {{-- BODY (collapsible) --}}
      <div class="order-body" id="body-{{ $i }}">
        @if($order->DetailPesanan->isNotEmpty())
          @foreach($order->DetailPesanan as $detail)
          <div class="item-row">
            <div class="flex-grow-1">
              <div class="item-name">{{ $detail->menu->nama_menu ?? '-' }} <span class="text-muted fw-normal">× {{ $detail->jumlah }}</span></div>
              @if(!empty($detail->topping))<div class="item-sub"><i class="bi bi-plus-circle me-1"></i>{{ collect($detail->topping)->pluck('nama_barang')->join(', ') }}</div>@endif
              @if(!empty($detail->catatan))<div class="item-sub"><i class="bi bi-pencil me-1"></i>{{ $detail->catatan }}</div>@endif
            </div>
            <div class="item-price">Rp {{ number_format($detail->subtotal,0,',','.') }}</div>
          </div>
          @endforeach
        @else
          <p class="text-muted small mb-0">Detail item tidak tersedia.</p>
        @endif

        <div class="d-flex justify-content-between align-items-center pt-3 mt-1">
          <span class="text-muted small fw-semibold">Total Pesanan</span>
          <span class="fw-bold" style="color:var(--merah);font-size:1.05rem;">Rp {{ number_format($itemTotal,0,',','.') }}</span>
        </div>

        {{-- Aksi pembayaran --}}
        @if(!$sudahBayar)
          @if($metode === 'qris')
          <div class="pay-box pay-box-warning mt-3">
            <div class="pay-icon">📱</div>
            <div class="flex-grow-1">
              <div class="pay-title">Pembayaran QRIS Belum Selesai</div>
              <div class="pay-desc mb-2">Sudah scan? Klik cek status, atau klik bayar untuk buka QR baru.</div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill"
                        onclick="cekStatus('{{ $order->id_pesanan }}', {{ $i }}, '{{ $bayar->midtrans_order_id ?? '' }}')"
                        id="btnCek-{{ $i }}">
                  <i class="bi bi-arrow-clockwise me-1"></i>Cek Status
                </button>
                <button class="btn btn-merah btn-sm flex-fill"
                        onclick="bayarQris('{{ $order->id_pesanan }}')"
                        id="btnBayar-{{ $i }}">
                  <i class="bi bi-qr-code me-1"></i>Bayar via QRIS
                </button>
              </div>
              <div id="statusMsg-{{ $i }}" class="mt-2" style="display:none;font-size:.8rem;"></div>
            </div>
          </div>
          @elseif($metode === 'tunai')
          <div class="pay-box pay-box-info mt-3">
            <div class="pay-icon">💵</div>
            <div>
              <div class="pay-title">Bayar Tunai di Kasir</div>
              <div class="pay-desc">Tunjukkan nomor <strong style="color:var(--merah);">{{ $order->id_pesanan }}</strong> ke kasir.</div>
            </div>
          </div>
          @elseif($metode === 'transfer' || $metode === 'bank_transfer')
          <div class="pay-box pay-box-warning mt-3">
            <div class="pay-icon">🏦</div>
            <div class="flex-grow-1">
              <div class="pay-title">Pembayaran Transfer Belum Selesai</div>
              <div class="pay-desc mb-2">Klik tombol di bawah untuk membuka halaman pembayaran Midtrans.</div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill"
                        onclick="cekStatus('{{ $order->id_pesanan }}', {{ $i }}, '{{ $bayar->midtrans_order_id ?? '' }}')"
                        id="btnCekTransfer-{{ $i }}">
                  <i class="bi bi-arrow-clockwise me-1"></i>Cek Status
                </button>
                <button class="btn btn-merah btn-sm flex-fill"
                        onclick="bayarTransfer('{{ $order->id_pesanan }}')"
                        id="btnBayarTransfer-{{ $i }}">
                  <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
                </button>
              </div>
              <div id="statusMsgTransfer-{{ $i }}" class="mt-2" style="display:none;font-size:.8rem;"></div>
            </div>
          </div>
          @endif
        @elseif($order->status_pemesanan === 'diproses')
        <div class="pay-box pay-box-info mt-3">
          <div class="pay-icon">👨‍🍳</div>
          <div><div class="pay-title">Sedang Diproses Dapur</div><div class="pay-desc">Pembayaran diterima. Pesananmu sedang disiapkan.</div></div>
        </div>
        @elseif($order->status_pemesanan === 'selesai')
        <div class="pay-box pay-box-success mt-3">
          <div class="pay-icon">✅</div>
          <div><div class="pay-title">Pesanan Selesai</div><div class="pay-desc">Terima kasih! Selamat menikmati Seblak Sangkuriang.</div></div>
        </div>
        @endif
      </div>
    </div>
    @endforeach
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle expand/collapse card
function toggleDetail(i) {
  const body  = document.getElementById('body-' + i);
  const chev  = document.getElementById('chev-' + i);
  const hdr   = chev.closest('.order-header');
  const open  = body.classList.contains('open');
  // Tutup semua
  document.querySelectorAll('.order-body').forEach(b => b.classList.remove('open'));
  document.querySelectorAll('.order-header').forEach(h => h.classList.remove('expanded'));
  // Buka yang diklik jika sebelumnya tertutup
  if (!open) {
    body.classList.add('open');
    hdr.classList.add('expanded');
  }
}

// Cek status pembayaran ke Midtrans
function cekStatus(idPesanan, i, dbOrderId) {
  const btn = document.getElementById('btnCek-' + i);
  const msg = document.getElementById('statusMsg-' + i);
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengecek...';
  msg.style.display = 'none';

  // Prioritas: localStorage (dari bayar terbaru) → DB order_id (dari blade) → tanpa order_id
  const latestOrderId = localStorage.getItem('midtrans_order_' + idPesanan) || dbOrderId || '';
  const url = '/pelanggan/cek-status/' + idPesanan + (latestOrderId ? '?order_id=' + encodeURIComponent(latestOrderId) : '');

  fetch(url, {
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
  })
  .then(r => r.json())
  .then(data => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Cek Status';

    if (data.status === 'lunas') {
      msg.style.display = 'block';
      msg.style.color = '#0f5132';
      msg.innerHTML = '✅ <strong>' + data.message + '</strong>';
      localStorage.removeItem('midtrans_order_' + idPesanan);
      setTimeout(() => window.location.reload(), 1500);
    } else if (data.status === 'pending') {
      msg.style.display = 'block';
      msg.style.color = '#856404';
      msg.innerHTML = '⏳ ' + data.message;
    } else if (data.status === 'batal') {
      msg.style.display = 'block';
      msg.style.color = '#842029';
      msg.innerHTML = '❌ ' + data.message;
      setTimeout(() => window.location.reload(), 2000);
    } else {
      msg.style.display = 'block';
      msg.style.color = '#555';
      msg.innerHTML = 'ℹ️ ' + (data.message || data.error || 'Status tidak diketahui');
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Cek Status';
    msg.style.display = 'block';
    msg.style.color = '#842029';
    msg.innerHTML = '❌ Gagal menghubungi server.';
  });
}

// Bayar via QRIS — ambil token baru lalu buka Snap
function bayarQris(idPesanan) {
  const overlay = document.getElementById('snapOverlay');
  overlay.classList.add('show');

  fetch('/pelanggan/bayar-qris/' + idPesanan, {
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    overlay.classList.remove('show');
    if (data.error) { alert('Error: ' + data.error); return; }

    // Simpan order_id terbaru untuk dipakai saat cek status
    localStorage.setItem('midtrans_order_' + idPesanan, data.order_id);

    window.snap.pay(data.snap_token, {
      onSuccess: function(result) {
        localStorage.setItem('midtrans_order_' + idPesanan, result.order_id);
        window.location.href = '{{ route("pelanggan.checkout.qris.success") }}?order_id=' + result.order_id;
      },
      onPending: function(result) {
        if (result.order_id) localStorage.setItem('midtrans_order_' + idPesanan, result.order_id);
        window.location.reload();
      },
      onError:   function() { alert('Pembayaran gagal. Silakan coba lagi.'); },
      onClose:   function() { /* user tutup popup */ }
    });
  })
  .catch(err => {
    overlay.classList.remove('show');
    alert('Gagal memuat pembayaran. Coba lagi.');
    console.error(err);
  });
}

// Bayar via Transfer — ambil token baru lalu buka Snap
function bayarTransfer(idPesanan) {
  const overlay = document.getElementById('snapOverlay');
  overlay.classList.add('show');

  fetch('/pelanggan/bayar-transfer/' + idPesanan, {
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    overlay.classList.remove('show');
    if (data.error) { alert('Error: ' + data.error); return; }

    // Simpan order_id terbaru untuk dipakai saat cek status
    localStorage.setItem('midtrans_order_' + idPesanan, data.order_id);

    window.snap.pay(data.snap_token, {
      onSuccess: function(result) {
        localStorage.setItem('midtrans_order_' + idPesanan, result.order_id);
        window.location.href = '{{ route("pelanggan.checkout.qris.success") }}?order_id=' + result.order_id;
      },
      onPending: function(result) {
        if (result.order_id) localStorage.setItem('midtrans_order_' + idPesanan, result.order_id);
        window.location.reload();
      },
      onError:   function() { alert('Pembayaran gagal. Silakan coba lagi.'); },
      onClose:   function() { /* user tutup popup */ }
    });
  })
  .catch(err => {
    overlay.classList.remove('show');
    alert('Gagal memuat pembayaran. Coba lagi.');
    console.error(err);
  });
}
</script>
</body>
</html>

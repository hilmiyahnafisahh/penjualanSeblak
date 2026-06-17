<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran QRIS | Seblak Sangkuriang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
  {{-- Snap.js HARUS di <head> --}}
  <script src="https://app.sandbox.midtrans.com/snap/snap.js"
          data-client-key="{{ config('services.midtrans.client_key') }}"></script>
  <style>
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
    .pay-card { width: 100%; max-width: 420px; }
    .pay-card-header {
      background: linear-gradient(135deg, var(--merah), #a32020);
      color: white; border-radius: 14px 14px 0 0;
      padding: 1.75rem 1.5rem 1.25rem; text-align: center;
    }
    .pay-card-body { background: white; border-radius: 0 0 14px 14px; padding: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,.12); }
    .info-row { background: var(--merah-muda); border-radius: 8px; padding: .65rem 1rem; margin-bottom: .5rem; display: flex; justify-content: space-between; align-items: center; }
    .info-row .label { font-size: .78rem; color: #888; }
    .info-row .val   { font-weight: 700; font-size: .92rem; }
    #loading-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; color: white; }
    .spinner { width: 46px; height: 46px; border: 4px solid rgba(255,255,255,.3); border-top-color: white; border-radius: 50%; animation: spin .8s linear infinite; margin-bottom: .9rem; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<div id="loading-overlay">
  <div class="spinner"></div>
  <div style="font-size:.9rem;">Memuat halaman pembayaran...</div>
</div>

<div class="pay-card">
  <div class="pay-card-header">
    <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo"
         style="width:60px;height:60px;object-fit:contain;border-radius:50%;border:2px solid rgba(255,255,255,.3);"
         class="mb-2">
    <div style="font-size:1.1rem; font-weight:800;">Pembayaran QRIS</div>
    <div style="font-size:.75rem; opacity:.8; margin-top:.2rem;">Scan QR untuk menyelesaikan pembayaran</div>
  </div>

  <div class="pay-card-body">
    <div class="info-row">
      <span class="label">No. Pesanan</span>
      <span class="val" style="color:var(--merah);">{{ $pemesanan->id_pesanan }}</span>
    </div>
    <div class="info-row">
      <span class="label">Total Pembayaran</span>
      <span class="val">Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</span>
    </div>
    <div class="info-row mb-4">
      <span class="label">Metode</span>
      <span class="badge px-3 py-2" style="background:var(--merah);font-size:.78rem;">📱 QRIS</span>
    </div>

    <div class="d-grid mb-3">
      <button id="pay-button" class="btn btn-merah py-3 fw-bold rounded-3" style="font-size:1rem;">
        📱 Tampilkan QR Pembayaran
      </button>
    </div>

    <div class="text-center">
      <a href="{{ route('pelanggan.riwayat') }}" class="text-muted small link-merah">Bayar nanti →</a>
    </div>
  </div>
</div>

<script>
var snapToken  = '{{ $snapToken }}';
var successUrl = '{{ route("pelanggan.checkout.qris.success") }}';
var riwayatUrl = '{{ route("pelanggan.riwayat") }}';

function bukaSnapPopup() {
  document.getElementById('loading-overlay').style.display = 'flex';
  window.snap.pay(snapToken, {
    onSuccess: function(result) {
      document.getElementById('loading-overlay').style.display = 'none';
      window.location.href = successUrl + '?order_id=' + result.order_id;
    },
    onPending: function() {
      document.getElementById('loading-overlay').style.display = 'none';
      window.location.href = riwayatUrl;
    },
    onError: function() {
      document.getElementById('loading-overlay').style.display = 'none';
      alert('Pembayaran gagal. Silakan coba lagi.');
    },
    onClose: function() {
      document.getElementById('loading-overlay').style.display = 'none';
    }
  });
}

document.getElementById('pay-button').addEventListener('click', bukaSnapPopup);
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(bukaSnapPopup, 500);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Berhasil | Seblak Sangkuriang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
  <style>
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
    .success-card { width: 100%; max-width: 440px; }
    .success-header { background: linear-gradient(135deg, #166534, #16a34a); color: white; border-radius: 14px 14px 0 0; padding: 2rem 1.5rem 1.5rem; text-align: center; }
    .success-body   { background: white; border-radius: 0 0 14px 14px; padding: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,.11); }
    .success-icon   { font-size: 3rem; margin-bottom: .75rem; }
    .info-row { background: var(--merah-muda); border-radius: 8px; padding: .65rem 1rem; margin-bottom: .5rem; display: flex; justify-content: space-between; align-items: center; }
    .info-row .label { font-size: .78rem; color: #888; }
    .info-row .val   { font-weight: 700; font-size: .92rem; }
    .kasir-banner { background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: .9rem 1rem; font-size: .82rem; color: #78350f; }
  </style>
</head>
<body>

<div class="success-card">
  <div class="success-header">
    <div class="success-icon">✅</div>
    <div style="font-size:1.2rem; font-weight:800;">Pesanan Berhasil!</div>
    <div style="font-size:.8rem; opacity:.85; margin-top:.3rem;">Silakan pergi ke kasir untuk pembayaran</div>
  </div>

  <div class="success-body">
    <div class="info-row">
      <span class="label">No. Pesanan</span>
      <span class="val" style="color:var(--merah);">{{ $pemesanan->id_pesanan }}</span>
    </div>
    <div class="info-row">
      <span class="label">Tanggal</span>
      <span class="val">{{ optional($pemesanan->tanggal_pemesanan)->format('d M Y, H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="label">Metode</span>
      <span class="badge px-3 py-2" style="background:#16a34a;font-size:.78rem;">💵 Tunai</span>
    </div>
    <div class="info-row">
      <span class="label">Total</span>
      <span class="val">Rp {{ number_format($grandTotal ?? $pemesanan->DetailPesanan->sum('subtotal'), 0, ',', '.') }}</span>
    </div>
    <div class="info-row mb-4">
      <span class="label">Status</span>
      <span class="pill pill-kuning"><i class="bi bi-clock me-1"></i>Menunggu Kasir</span>
    </div>

    <div class="kasir-banner mb-4">
      <div class="fw-bold mb-1"><i class="bi bi-geo-alt-fill me-1"></i>Tunjukkan ke Kasir</div>
      Nomor pesanan <strong class="text-danger">{{ $pemesanan->id_pesanan }}</strong> — kasir akan memproses pembayaran Anda.
    </div>

    <div class="d-grid gap-2">
      <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-merah py-2 fw-bold rounded-3">
        <i class="bi bi-clock-history me-1"></i>Lihat Riwayat Pesanan
      </a>
      <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-outline-secondary py-2 rounded-3">
        Kembali ke Menu
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

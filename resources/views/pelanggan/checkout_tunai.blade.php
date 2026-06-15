<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fdf0f0; font-family: 'Segoe UI', sans-serif; }
        .card-sukses {
            max-width: 480px; margin: 5rem auto;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,.08);
            border: none;
        }
        .icon-sukses {
            width: 80px; height: 80px; border-radius: 50%;
            background: #d1e7dd;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.2rem;
            font-size: 2.5rem;
        }
        .btn-merah { background: #8b1a1a; color: white; border: none; border-radius: .75rem; }
        .btn-merah:hover { background: #600e0e; color: white; }
        .info-row { background: #fdf0f0; border-radius: .75rem; padding: .85rem 1rem; margin-bottom: .6rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card card-sukses p-4 text-center">

        <div class="icon-sukses">✅</div>

        <h4 class="fw-bold mb-1" style="color:#0f5132;">Pesanan Berhasil!</h4>
        <p class="text-muted mb-4">Silahkan pergi ke kasir untuk melakukan pembayaran.</p>

        <div class="text-start mb-4">
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted">No. Pesanan</span>
                <strong style="color:#8b1a1a;">{{ $pemesanan->id_pesanan }}</strong>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted">Tanggal</span>
                <span>{{ optional($pemesanan->tanggal_pemesanan)->format('d M Y, H:i') }}</span>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted">Metode</span>
                <span class="badge bg-success fs-6">💵 Tunai</span>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted">Total</span>
                <strong>Rp {{ number_format($pemesanan->subtotal, 0, ',', '.') }}</strong>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted">Status</span>
                <span class="badge bg-warning text-dark">Menunggu Pembayaran di Kasir</span>
            </div>
        </div>

        <div class="alert alert-warning text-start" role="alert" style="border-radius:.75rem;">
            <strong>📍 Informasi Kasir:</strong><br>
            Tunjukkan nomor pesanan <strong>{{ $pemesanan->id_pesanan }}</strong> kepada kasir untuk menyelesaikan pembayaran.
        </div>

        <div class="d-grid gap-2 mt-3">
            <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-merah py-2">
                Lihat Riwayat Pesanan
            </a>
            <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-outline-secondary py-2">
                Kembali ke Menu
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

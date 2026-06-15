<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS | Seblak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Snap.js HARUS dimuat di <head> dengan data-client-key --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <style>
        body { background: #fdf0f0; font-family: 'Segoe UI', sans-serif; }
        .card-bayar {
            max-width: 480px; margin: 4rem auto;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,.08);
            border: none;
        }
        .btn-merah { background: #8b1a1a; color: white; border: none; border-radius: .75rem; }
        .btn-merah:hover { background: #600e0e; color: white; }
        .info-row { background: #fdf0f0; border-radius: .75rem; padding: .75rem 1rem; margin-bottom: .5rem; }
        #loading-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 9999;
            align-items: center; justify-content: center; flex-direction: column; color: white;
        }
        .spinner { width: 48px; height: 48px; border: 5px solid #fff3;
            border-top-color: #fff; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

{{-- Loading overlay saat popup muncul --}}
<div id="loading-overlay">
    <div class="spinner mb-3"></div>
    <div style="font-size:1rem;">Memuat halaman pembayaran...</div>
</div>

<div class="container">
    <div class="card card-bayar p-4">

        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-seblak.png') }}" alt="Logo"
                 style="width:64px;height:64px;object-fit:contain;border-radius:50%;" class="mb-2">
            <h5 class="fw-bold mb-0" style="color:#8b1a1a;">Pembayaran QRIS</h5>
            <p class="text-muted small mb-0">Scan QR untuk menyelesaikan pembayaran</p>
        </div>

        <div class="mb-4">
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted small">No. Pesanan</span>
                <strong style="color:#8b1a1a;">{{ $pemesanan->id_pesanan }}</strong>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted small">Total Pembayaran</span>
                <strong>Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</strong>
            </div>
            <div class="info-row d-flex justify-content-between">
                <span class="text-muted small">Metode</span>
                <span class="badge fs-6 px-3 py-2" style="background:#8b1a1a;">📱 QRIS</span>
            </div>
        </div>

        <div class="d-grid mb-3">
            <button id="pay-button" class="btn btn-merah py-3 fw-bold fs-5">
                📱 Tampilkan QR Pembayaran
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('pelanggan.riwayat') }}" class="text-muted small">Bayar nanti</a>
        </div>

    </div>
</div>

<script>
    var snapToken = '{{ $snapToken }}';
    var successUrl = '{{ route("pelanggan.checkout.qris.success") }}';
    var riwayatUrl = '{{ route("pelanggan.riwayat") }}';

    function bukaSnapPopup() {
        document.getElementById('loading-overlay').style.display = 'flex';

        window.snap.pay(snapToken, {
            onSuccess: function (result) {
                document.getElementById('loading-overlay').style.display = 'none';
                window.location.href = successUrl + '?order_id=' + result.order_id;
            },
            onPending: function (result) {
                document.getElementById('loading-overlay').style.display = 'none';
                window.location.href = riwayatUrl;
            },
            onError: function (result) {
                document.getElementById('loading-overlay').style.display = 'none';
                alert('Pembayaran gagal. Silahkan coba lagi.');
            },
            onClose: function () {
                document.getElementById('loading-overlay').style.display = 'none';
            }
        });
    }

    // Klik tombol
    document.getElementById('pay-button').addEventListener('click', bukaSnapPopup);

    // Auto-buka popup saat halaman siap
    document.addEventListener('DOMContentLoaded', function () {
        // Delay sedikit agar Snap.js selesai init
        setTimeout(bukaSnapPopup, 500);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

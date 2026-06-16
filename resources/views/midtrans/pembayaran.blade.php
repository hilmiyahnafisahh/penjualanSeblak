<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Midtrans</title>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7fafc; color: #1a202c; }
        .card { max-width: 560px; margin: auto; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,.08); }
        .header { margin-bottom: 1.5rem; }
        .header h1 { margin: 0 0 .5rem; font-size: 1.5rem; }
        .detail { margin-bottom: 1.25rem; }
        .detail div { margin-bottom: .75rem; }
        .button { display: inline-block; padding: .9rem 1.4rem; border: none; border-radius: 8px; background: #2563eb; color: #fff; font-size: 1rem; cursor: pointer; }
        .button:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Pembayaran Pesanan</h1>
            <p>Silakan lanjutkan pembayaran untuk pesanan berikut.</p>
        </div>

        <div class="detail">
            <div><strong>No Pembayaran:</strong> {{ $pembayaran->id_pembayaran }}</div>
            <div><strong>No Pesanan:</strong> {{ $pemesanan->id_pesanan }}</div>
            <div><strong>Total:</strong> Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</div>
            <div><strong>Metode:</strong> {{ strtoupper($pembayaran->metode_pembayaran) }}</div>
        </div>

        <button id="pay-button" class="button">Lanjutkan ke Midtrans</button>
        @unless(config('services.midtrans.client_key'))
            <div style="margin-top:1rem;color:#b91c1c;font-weight:600;">MIDTRANS_CLIENT_KEY belum dikonfigurasi. Silakan tambahkan ke .env atau config/services.php.</div>
        @endunless
    </div>

    <script>
        let paymentId = {{ $pembayaran->id }};
        let isProcessing = false;

        document.getElementById('pay-button').addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    // Mulai polling status
                    pollPaymentStatus();
                },
                onPending: function(result){
                    // Mulai polling status untuk pending
                    pollPaymentStatus();
                },
                onError: function(result){
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function(){
                    alert('Anda menutup pop-up pembayaran sebelum menyelesaikan transaksi.');
                }
            });
        });

        // Polling untuk cek status pembayaran
        function pollPaymentStatus() {
            if (isProcessing) return;
            isProcessing = true;

            const maxAttempts = 30; // 30 detik (dengan interval 1 detik)
            let attempts = 0;

            const pollInterval = setInterval(function() {
                attempts++;

                fetch(`/api/pembayaran/${paymentId}/status`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Status pembayaran:', data);

                        // Jika status sudah lunas, redirect
                        if (data.status === 'lunas') {
                            clearInterval(pollInterval);
                            alert('Pembayaran berhasil!');
                            window.location.href = '{{ url('/admin/pembayaran') }}';
                        }
                        // Jika batal, redirect dengan pesan
                        else if (data.status === 'batal') {
                            clearInterval(pollInterval);
                            alert('Pembayaran dibatalkan.');
                            window.location.href = '{{ url('/admin/pembayaran') }}';
                        }
                        // Jika masih pending setelah max attempts, arahkan ke halaman pembayaran
                        else if (attempts >= maxAttempts) {
                            clearInterval(pollInterval);
                            window.location.href = '{{ url('/admin/pembayaran') }}';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking payment status:', error);
                        if (attempts >= maxAttempts) {
                            clearInterval(pollInterval);
                            window.location.href = '{{ url('/admin/pembayaran') }}';
                        }
                    });
            }, 1000); // Polling setiap 1 detik
        }
    </script>
</body>
</html>

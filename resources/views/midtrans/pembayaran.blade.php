<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Midtrans</title>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
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
    </div>

    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    window.location.href = '{{ url('/admin/pembayaran') }}';
                },
                onPending: function(result){
                    window.location.href = '{{ url('/admin/pembayaran') }}';
                },
                onError: function(result){
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function(){
                    alert('Anda menutup pop-up pembayaran sebelum menyelesaikan transaksi.');
                }
            });
        });
    </script>
</body>
</html>

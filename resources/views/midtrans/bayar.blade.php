<!DOCTYPE html>
<html>
<head>
    <title>Bayar Beban</title>

    <!-- Midtrans Snap -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body>

    <h2>Pembayaran Beban</h2>

    <p>Total: Rp {{ number_format($beban->total, 0, ',', '.') }}</p>

    <button id="pay-button">Bayar Sekarang</button>//tombol bayar

    <script>
        document.getElementById('pay-button').onclick = function () {  // Ketika tombol bayar diklik, panggil fungsi snap.pay dengan snapToken yang diberikan dari controller
            snap.pay('{{ $snapToken }}', { //diambil dari controller
                onSuccess: function(result){
                    alert("Pembayaran berhasil");
                    window.location.href = "/admin/catat-bebans";
                },
                onPending: function(result){
                    alert("Menunggu pembayaran");
                },
                onError: function(result){
                    alert("Pembayaran gagal");
                }
            });
        };
    </script>

</body>
</html>
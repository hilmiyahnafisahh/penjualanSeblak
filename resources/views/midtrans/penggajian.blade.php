<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Penggajian</title>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #f7fafc;
            color: #1a202c;
        }
        .card { 
            max-width: 560px; 
            margin: auto; 
            background: white; 
            padding: 1.5rem; 
            border-radius: 12px; 
            box-shadow: 0 20px 40px rgba(0,0,0,.08); 
        }
        .header { 
            margin-bottom: 1.5rem; 
             text-align: center; 
        }
        .header h1 {
            margin: 0 0 .5rem; 
            font-size: 1.5rem; }
        .detail { 
            margin-bottom: 1.25rem; }
        .detail div { 
            margin-bottom: .75rem; }
        .button { 
            display: inline-block; 
            padding: .9rem 1.4rem; 
            border: none; 
            border-radius: 8px; 
            background: #16a34a; 
            color: #fff; 
            font-size: 1rem; 
            cursor: pointer; }
        .button:hover { background: #15803d; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Pembayaran Gaji</h1>
            <p>Bayar gaji karyawan berikut melalui Midtrans.</p>
        </div>

        <div class="detail">
            <div><strong>ID Penggajian:</strong> {{ $penggajian->id_penggajian }}</div>
            <div><strong>Karyawan:</strong> {{ $penggajian->karyawan->nama ?? 'Tidak Diketahui' }}</div>
            <div><strong>Periode:</strong> {{ $penggajian->periode }}</div>
            <div><strong>Total Gaji:</strong> Rp {{ number_format($penggajian->nominal, 0, ',', '.') }}</div>
        </div>

        <button id="pay-button" class="button">Bayar Gaji Sekarang</button>
        @unless(config('services.midtrans.client_key'))
            <div style="margin-top:1rem;color:#b91c1c;font-weight:600;">
                MIDTRANS_CLIENT_KEY belum dikonfigurasi. Silakan tambahkan ke .env atau config/services.php.
            </div>
        @endunless
    </div>

    <script>
        function updatePenggajian(result) { //update statusgaji server
            return fetch('{{ route('penggajian.midtrans.success', ['id' => $penggajian->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(result)
            });
        }

        document.getElementById('pay-button').addEventListener('click', function () { //event listener untuk tombol pembayaran, ketika tombol diklik, maka akan memanggil fungsi snap.pay dari Midtrans dengan snapToken yang sudah disiapkan, fungsi ini akan membuka pop-up pembayaran Midtrans dan menangani berbagai hasil pembayaran seperti sukses, pending, error, atau jika pengguna menutup pop-up sebelum menyelesaikan transaksi
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    updatePenggajian(result).finally(function() {
                        window.location.href = '{{ url('/admin/penggajians') }}'; 
                    });
                },
                onPending: function(result){
                    updatePenggajian(result).finally(function() {
                        window.location.href = '{{ url('/admin/penggajians') }}'; 
                    });
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

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Pemesanan</title>
</head>
<body>
    <h1>Invoice Pemesanan</h1>
    <p>Halo {{ $pemesanan->pelanggan->nama_pelanggan ?? 'Pelanggan' }},</p>
    <p>Terima kasih telah melakukan pemesanan.</p>
    <p>
        <strong>No. Faktur:</strong> {{ $pemesanan->id_pesanan }}<br>
        <strong>Tanggal:</strong> {{ $pemesanan->tanggal_pemesanan }}<br>
        <strong>Total:</strong> Rp {{ number_format($pemesanan->subtotal, 0, ',', '.') }}
    </p>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pemesanan->DetailPesanan as $detail)
                <tr>
                    <td>{{ $detail->menu?->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($detail->topping && is_array($detail->topping))
                    @foreach($detail->topping as $topping)
                        <tr>
                            <td>&nbsp;&nbsp;- {{ \App\Models\Barang::find($topping['id_barang'] ?? null)?->nama_barang ?? 'Topping' }}</td>
                            <td>{{ $topping['qty'] ?? 0 }}</td>
                            <td>Rp {{ number_format($topping['harga'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($topping['subtotal'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                <td><strong>Rp {{ number_format($pemesanan->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
    <p>Silakan simpan email ini sebagai bukti pembayaran.</p>
</body>
</html>
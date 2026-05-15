<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Pembayaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Daftar Pembayaran</h2>
    <table>
        <thead>
            <tr>
                <th>No Pembayaran</th>
                <th>No Pesanan</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th>Status</th>
                <th class="text-right">Total</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembayaran as $item)
                <tr>
                    <td>{{ $item->id_pembayaran }}</td>
                    <td>{{ $item->pemesanan?->id_pesanan ?? '-' }}</td>
                    <td>{{ $item->pemesanan?->pelanggan?->nama_pelanggan ?? '-' }}</td>
                    <td>{{ ucfirst($item->metode_pembayaran) }}</td>
                    <td>{{ ucfirst($item->status_pembayaran) }}</td>
                    <td class="text-right">{{ 'Rp ' . number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                    <td>{{ optional($item->tanggal_pembayaran)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

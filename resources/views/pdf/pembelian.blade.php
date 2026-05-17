<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .lunas { color: green; font-weight: bold; }
        .hutang { color: red; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Laporan Pembelian</h2>
    <table>
        <thead>
            <tr>
                <th>Faktur</th>
                <th>Karyawan</th>
                <th>Status</th>
                <th class="text-right">Total Bayar</th>
                <th class="text-right">Sisa Tagihan</th>
                <th>Vendor</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembelian as $item)
            <tr>
                <td>{{ $item->id_pembelian }}</td>
                <td>{{ $item->karyawan->nama ?? '-' }}</td>
                <td class="{{ $item->status }}">{{ strtoupper($item->status) }}</td>
                <td class="text-right">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                <td class="text-right">
                    Rp {{ number_format($item->pembayaran->first()->sisa_tagihan ?? 0, 0, ',', '.') }}
                </td>
                <td>{{ $item->pembayaran->first()->nama_vendor ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tgl)->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
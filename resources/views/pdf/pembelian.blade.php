<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        td {
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .lunas {
            color: green;
            font-weight: bold;
        }

        .hutang {
            color: red;
            font-weight: bold;
        }
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
                <th>Total Bayar</th>
                <th>Sisa Tagihan</th>
                <th>Vendor</th>
                <th>Tanggal</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pembelian as $item)
                <tr>
                    <td>
                        {{ $item->id_pembelian ?? '-' }}
                    </td>

                    <td>
                        {{ $item->karyawan->nama ?? '-' }}
                    </td>

                    <td class="text-center">
                        <span class="{{ $item->status }}">
                            {{ strtoupper($item->status ?? '-') }}
                        </span>
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($item->total_bayar ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format(optional($item->pembayaran->first())->sisa_tagihan ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ optional($item->pembayaran->first())->nama_vendor ?? '-' }}
                    </td>

                    <td>
                        {{ $item->tgl 
                            ? \Carbon\Carbon::parse($item->tgl)->format('d M Y H:i')
                            : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        Data pembelian tidak tersedia
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
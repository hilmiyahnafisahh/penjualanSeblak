<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Beban</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
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
            padding: 6px 8px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

    <h2>Laporan Beban</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis Beban</th>
                <th>Keterangan</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @php $grandTotal = 0; @endphp

            @foreach($data as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->jenis_beban }}</td>
                <td>{{ $row->keterangan }}</td>
                <td class="text-right">
                    Rp {{ number_format($row->total, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    @if($row->status == 'lunas')
                        <span style="color: green;">LUNAS</span>
                    @else
                        <span style="color: red;">BELUM LUNAS</span>
                    @endif
                </td>
            </tr>

            @php $grandTotal += $row->total; @endphp
            @endforeach

            <!-- TOTAL SEMUA -->
            <tr>
                <td colspan="4" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td class="text-right">
                    <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
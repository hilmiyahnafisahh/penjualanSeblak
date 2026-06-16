<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            padding: 28px 32px;
        }

        /* HEADER */
        .header-table { width: 100%; border: none; }
        .header-table td { border: none; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 3px; }
        .company-detail { font-size: 10px; color: #6b7280; line-height: 1.6; }
        .report-title { text-align: right; }
        .report-title h1 { font-size: 18px; font-weight: bold; color: #7f1d1d; margin-bottom: 3px; }
        .report-title p { color: #6b7280; font-size: 10px; }
        .divider { border: none; border-top: 2px solid #7f1d1d; margin: 12px 0 16px; }

        /* PERIODE */
        .periode-badge {
            display: inline-block;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 4px;
            padding: 5px 12px;
            font-size: 11px;
            color: #7f1d1d;
            margin-bottom: 16px;
        }

        /* TABLE */
        .section-title { font-size: 12px; font-weight: bold; color: #111827; margin-bottom: 8px; }
        .table-wrapper { border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #7f1d1d;
            color: #fff;
            padding: 9px 8px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        thead th.left   { text-align: left; }
        thead th.right  { text-align: right; }
        thead th.center { text-align: center; }
        tbody td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) { background: #fef9f9; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        /* TIPE BADGE */
        .badge-menu    { background: #f0fdf4; color: #15803d; font-size: 9px; padding: 2px 6px; border-radius: 8px; }
        .badge-topping { background: #eff6ff; color: #1d4ed8; font-size: 9px; padding: 2px 6px; border-radius: 8px; }

        /* FOOTER */
        tfoot td {
            padding: 9px 8px;
            font-weight: bold;
            background: #fef2f2;
            border-top: 2px solid #7f1d1d;
            font-size: 11px;
        }
        .page-footer {
            margin-top: 20px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">Seblak Sangkuriang</div>
                <div class="company-detail">Bandung, Indonesia &bull; admin@seblak.com</div>
            </td>
            <td class="report-title">
                <h1>LAPORAN PENJUALAN</h1>
                <p>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</p>
            </td>
        </tr>
    </table>
    <hr class="divider">

    <!-- PERIODE -->
    <div class="periode-badge"><strong>Periode:</strong> {{ $rangeLabel }}</div>

    <!-- TABEL DETAIL PENJUALAN -->
    <div class="section-title">Detail Penjualan</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="center" width="4%">No</th>
                    <th class="left"   width="13%">Tanggal</th>
                    <th class="left"   width="35%">Nama Produk</th>
                    <th class="center" width="10%">Tipe</th>
                    <th class="right"  width="8%">Qty</th>
                    <th class="right"  width="15%">Harga Satuan</th>
                    <th class="right"  width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportRows as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['tanggal'] }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td class="text-center">
                            @if(($row['tipe'] ?? '') === 'topping')
                                <span class="badge-topping">Topping</span>
                            @else
                                <span class="badge-menu">Menu</span>
                            @endif
                        </td>
                        <td class="text-right">{{ $row['jumlah'] }}</td>
                        <td class="text-right">Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:16px; color:#6b7280;">
                            Tidak ada data penjualan untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportRows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">TOTAL</td>
                    <td class="text-right">{{ $reportRows->sum('jumlah') }}</td>
                    <td></td>
                    <td class="text-right" style="color:#15803d;">
                        Rp {{ number_format($reportRows->sum('total'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="page-footer">
        Laporan digenerate otomatis &bull; Seblak Sangkuriang &bull; {{ now()->format('d F Y H:i') }}
    </div>

</body>
</html>

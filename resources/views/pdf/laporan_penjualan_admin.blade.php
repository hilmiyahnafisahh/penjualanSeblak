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

        /* ── HEADER ── */
        .header-table { width: 100%; border: none; margin-bottom: 0; }
        .header-table td { border: none; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 3px; }
        .company-detail { font-size: 10px; color: #6b7280; line-height: 1.6; }
        .report-title { text-align: right; }
        .report-title h1 { font-size: 18px; font-weight: bold; color: #7f1d1d; margin-bottom: 3px; }
        .report-title p { color: #6b7280; font-size: 10px; }
        .divider { border: none; border-top: 2px solid #7f1d1d; margin: 12px 0 18px; }

        /* ── PERIODE BADGE ── */
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

        /* ── SUMMARY ── */
        .summary-table { width: 100%; border: none; margin-bottom: 18px; }
        .summary-table td { border: none; padding: 0 6px 0 0; }
        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 10px 12px;
            background: #fafafa;
        }
        .summary-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
        .summary-value { font-size: 15px; font-weight: bold; color: #111827; }
        .summary-value.green { color: #15803d; }

        /* ── TOP PRODUCT ── */
        .top-product {
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 10px 14px;
            background: #fefce8;
            margin-bottom: 18px;
            font-size: 11px;
        }
        .top-product strong { color: #92400e; }

        /* ── MAIN TABLE ── */
        .table-wrapper { border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; margin-bottom: 18px; }
        table.main { width: 100%; border-collapse: collapse; }
        table.main thead th {
            background: #7f1d1d;
            color: #fff;
            padding: 9px 8px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        table.main thead th.left  { text-align: left; }
        table.main thead th.right { text-align: right; }
        table.main thead th.center { text-align: center; }
        table.main tbody td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: top;
        }
        table.main tbody tr:nth-child(even) { background: #fef2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── TOP PRODUCTS TABLE ── */
        .section-title { font-size: 12px; font-weight: bold; color: #111827; margin-bottom: 8px; }
        table.top { width: 100%; border-collapse: collapse; }
        table.top thead th {
            background: #374151;
            color: #fff;
            padding: 8px;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.top thead th.right { text-align: right; }
        table.top tbody td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table.top tbody tr:nth-child(even) { background: #f9fafb; }

        /* ── FOOTER ── */
        .page-footer {
            margin-top: 24px;
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
                <div class="company-detail">
                    Bandung, Indonesia &nbsp;&bull;&nbsp; admin@seblak.com
                </div>
            </td>
            <td class="report-title">
                <h1>LAPORAN PENJUALAN</h1>
                <p>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</p>
            </td>
        </tr>
    </table>
    <hr class="divider">

    <!-- PERIODE -->
    <div class="periode-badge">
        <strong>Periode:</strong> {{ $rangeLabel }}
    </div>

    <!-- SUMMARY BOXES -->
    <table class="summary-table">
        <tr>
            <td width="33%">
                <div class="summary-box">
                    <div class="summary-label">Total Pesanan</div>
                    <div class="summary-value">{{ $summary['total_orders'] }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="summary-box">
                    <div class="summary-label">Total Item Terjual</div>
                    <div class="summary-value">{{ $summary['total_qty'] }}</div>
                </div>
            </td>
            <td width="34%">
                <div class="summary-box">
                    <div class="summary-label">Total Pendapatan</div>
                    <div class="summary-value green">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TOP PRODUCT -->
    @if($topProduct)
    <div class="top-product">
        🏆 <strong>Produk Terlaris:</strong>
        {{ $topProduct['nama'] }}
        &mdash; {{ $topProduct['jumlah'] }} terjual
        &mdash; Rp {{ number_format($topProduct['total'], 0, ',', '.') }}
    </div>
    @endif

    <!-- DETAIL PENJUALAN -->
    <div class="section-title">Detail Penjualan</div>
    <div class="table-wrapper">
        <table class="main">
            <thead>
                <tr>
                    <th class="center" width="4%">No</th>
                    <th class="left"   width="14%">Tanggal</th>
                    <th class="left"   width="20%">Pelanggan</th>
                    <th class="left"   width="28%">Nama Produk</th>
                    <th class="right"  width="10%">Qty</th>
                    <th class="right"  width="12%">Harga Satuan</th>
                    <th class="right"  width="12%">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportRows as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['tanggal'] }}</td>
                        <td>{{ $row['pelanggan'] }}</td>
                        <td>{{ $row['nama'] }}</td>
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
                    <td colspan="4" style="padding:9px 8px; font-weight:bold; text-align:right; background:#fef2f2; font-size:11px; border-top:2px solid #7f1d1d;">
                        TOTAL
                    </td>
                    <td style="padding:9px 8px; font-weight:bold; text-align:right; background:#fef2f2; border-top:2px solid #7f1d1d;">
                        {{ $reportRows->sum('jumlah') }}
                    </td>
                    <td style="background:#fef2f2; border-top:2px solid #7f1d1d;"></td>
                    <td style="padding:9px 8px; font-weight:bold; text-align:right; background:#fef2f2; color:#15803d; font-size:11px; border-top:2px solid #7f1d1d;">
                        Rp {{ number_format($reportRows->sum('total'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- TOP PRODUCTS BREAKDOWN -->
    @if($topProducts->isNotEmpty())
    <div class="section-title" style="margin-top:12px;">Penjualan Per Item</div>
    <div class="table-wrapper">
        <table class="top">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px; width:5%;">No</th>
                    <th style="text-align:left; padding:8px;">Nama Produk</th>
                    <th class="right" style="padding:8px; width:15%;">Qty Terjual</th>
                    <th class="right" style="padding:8px; width:20%;">Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $prod)
                    <tr>
                        <td style="padding:7px 8px;">{{ $i + 1 }}</td>
                        <td style="padding:7px 8px;">{{ $prod['nama'] }}</td>
                        <td style="padding:7px 8px; text-align:right;">{{ $prod['jumlah'] }}</td>
                        <td style="padding:7px 8px; text-align:right;">Rp {{ number_format($prod['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="page-footer">
        Laporan digenerate otomatis &bull; Seblak Sangkuriang &bull; {{ now()->format('d F Y H:i') }}
    </div>

</body>
</html>

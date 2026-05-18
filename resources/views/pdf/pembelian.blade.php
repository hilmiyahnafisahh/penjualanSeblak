<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pencatatan Pembelian</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2b2b2b;
            padding: 30px;
            background: #ffffff;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
        }

        .header-table {
            width: 100%;
            border: none;
        }

        .header-table td {
            border: none;
            vertical-align: top;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }

        .company-detail {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        .report-title {
            text-align: right;
        }

        .report-title h1 {
            font-size: 22px;
            color: #111827;
            margin-bottom: 6px;
        }

        .report-title p {
            color: #6b7280;
            font-size: 11px;
        }

        .divider {
            border-bottom: 2px solid #111827;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary-table {
            width: 100%;
            border: none;
        }

        .summary-table td {
            border: none;
            padding: 0;
        }

        .summary-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            margin-right: 10px;
        }

        .summary-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }

        .table-wrapper {
            border: 1px solid #d1d5db;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #111827;
            color: white;
            padding: 12px 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            text-align: center;
        }

        tbody td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }

        .status-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .status-hutang {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">

        <table class="header-table">
            <tr>

                <td>
                    <div class="company-name">
                        SEBLAK
                    </div>

                    <div class="company-detail">
                        Sistem Manajemen Seblak<br>
                        Bandung, Indonesia<br>
                        Email: admin@seblak.com
                    </div>
                </td>

                <td class="report-title">
                    <h1>LAPORAN PEMBELIAN</h1>

                    <p>
                        Dicetak:
                        {{ now()->format('d F Y H:i') }}
                    </p>
                </td>

            </tr>
        </table>

        <div class="divider"></div>

    </div>

    <!-- SUMMARY -->
    <div class="summary">

        <table class="summary-table">
            <tr>

                <td width="33%">
                    <div class="summary-box">

                        <div class="summary-label">
                            TOTAL TRANSAKSI
                        </div>

                        <div class="summary-value">
                            {{ $pembelian->count() }}
                        </div>

                    </div>
                </td>

                <td width="33%">
                    <div class="summary-box">

                        <div class="summary-label">
                            TOTAL PEMBELIAN
                        </div>

                        <div class="summary-value">
                            Rp {{ number_format($pembelian->sum('total_bayar'), 0, ',', '.') }}
                        </div>

                    </div>
                </td>

                <td width="33%">
                    <div class="summary-box">

                        <div class="summary-label">
                            TOTAL HUTANG
                        </div>

                        <div class="summary-value">
                            Rp
                            {{
                                number_format(
                                    $pembelian->sum(function ($item) {
                                        return optional($item->pembayaran->first())->sisa_tagihan ?? 0;
                                    }),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}
                        </div>

                    </div>
                </td>

            </tr>
        </table>

    </div>

    <!-- TABLE -->
    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th width="14%">Faktur</th>
                    <th width="16%">Karyawan</th>
                    <th width="12%">Status</th>
                    <th width="16%">Total Bayar</th>
                    <th width="16%">Sisa Tagihan</th>
                    <th width="14%">Vendor</th>
                    <th width="12%">Tanggal</th>
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

                            @if($item->status == 'lunas')

                                <span class="status status-lunas">
                                    LUNAS
                                </span>

                            @else

                                <span class="status status-hutang">
                                    HUTANG
                                </span>

                            @endif

                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->total_bayar ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp
                            {{
                                number_format(
                                    optional($item->pembayaran->first())->sisa_tagihan ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}
                        </td>

                        <td>
                            {{ optional($item->pembayaran->first())->nama_vendor ?? '-' }}
                        </td>

                        <td class="text-center">
                            {{
                                $item->tgl
                                ? \Carbon\Carbon::parse($item->tgl)->format('d/m/Y')
                                : '-'
                            }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty">
                            Data pembelian tidak tersedia
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</body>

</html>
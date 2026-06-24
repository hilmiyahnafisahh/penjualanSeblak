<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Umum - {{ $periodeLabel }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #eee; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Jurnal Umum</h1>
        <div class="subtitle">{{ $periodeLabel }}</div>
        <div class="subtitle">Seblak Sangkuriang — Dicetak {{ now()->format('d/m/Y H:i') }}</div>

    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Akun</th>
                <th>Reff</th>
                <th class="text-right">Debet</th>
                <th class="text-right">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($jurnals as $jurnal)
                @foreach($jurnal->jurnaldetail as $detail)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ optional($jurnal->tgl) ? \Carbon\Carbon::parse($jurnal->tgl)->format('Y-m-d') : '-' }}</td>
                        <td>{{ optional($detail->akun)->nama_akun ?? '-' }}</td>
                        <td>{{ $jurnal->no_referensi ?? '-' }}</td>
                        <td class="text-right">{{ $detail->debit ? rupiah($detail->debit) : '' }}</td>
                        <td class="text-right">{{ $detail->credit ? rupiah($detail->credit) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div style="text-align:right;margin-top:8px;font-weight:bold;">
        Total Debet: {{ rupiah($jurnals->flatMap->jurnaldetail->sum('debit')) }}
        <br>
        Total Kredit: {{ rupiah($jurnals->flatMap->jurnaldetail->sum('credit')) }}
    </div>
</body>
</html>

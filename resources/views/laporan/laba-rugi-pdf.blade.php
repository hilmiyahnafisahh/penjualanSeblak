<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi - {{ $periodeLabel }}</title>
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
        <h2>Laporan Laba Rugi</h2>
        <div>{{ $periodeLabel }}</div>
    </div>

    <h4>Pendapatan</h4>
    <table>
        <thead>
            <tr><th>Kode</th><th>Akun</th><th class="text-right">Jumlah</th></tr>
        </thead>
        <tbody>
            @forelse($pendapatanGroups as $p)
            <tr>
                <td>{{ $p['kode'] }}</td>
                <td>{{ $p['nama'] }}</td>
                <td class="text-right">{{ rupiah($p['jumlah']) }}</td>
            </tr>
            @empty
            <tr><td colspan="3">Tidak ada pendapatan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align:right;margin-top:8px;font-weight:bold;">Total Pendapatan: {{ rupiah($totalPendapatan) }}</div>

    <h4 style="margin-top:18px;">Beban</h4>
    <table>
        <thead>
            <tr><th>Kode</th><th>Akun</th><th class="text-right">Jumlah</th></tr>
        </thead>
        <tbody>
            @forelse($bebanGroups as $b)
            <tr>
                <td>{{ $b['kode'] }}</td>
                <td>{{ $b['nama'] }}</td>
                <td class="text-right">{{ rupiah($b['jumlah']) }}</td>
            </tr>
            @empty
            <tr><td colspan="3">Tidak ada beban.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align:right;margin-top:8px;font-weight:bold;">Total Beban: {{ rupiah($totalBeban) }}</div>

    <h3 style="text-align:right;margin-top:18px;">Laba Bersih: {{ rupiah($labaBersih) }}</h3>
</body>
</html>

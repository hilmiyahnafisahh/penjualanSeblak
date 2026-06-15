<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Penggajian</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; } /* 👈 Tambahkan ini */
    </style>
</head>
<body>
    <h2>Daftar Penggajian</h2>
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th style="text-align: center;">Periode</th>
                <th style="text-align: center;">Tanggal</th>
                <th style="text-align: center;">jam kerja</th>
                <th style="text-align: center;">upah per jam</th>
                <th style="text-align: center;">kehadiran</th>
                <th style="text-align: center;">Gaji</th>
                <th style="text-align: center;">status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penggajian as $p)
            <tr>
                <td>{{ $p->karyawan->nama }}</td>
                <td style="text-align: center;">{{ $p->periode }}</td>
                <td style="text-align: center;">{{ $p->tanggal_penggajian }}</td>
                <td style="text-align: center;">{{ $p->jam_kerja }}</td>
                <td style="text-align: center;">{{ rupiah($p->upah_per_jam) }}</td>
                <td style="text-align: center;">{{ $p->kehadiran }}</td>
                <td class="text-right">{{ rupiah($p->nominal) }}</td>
                <td style="text-align: center;">
                    @if($p->status == 'Dibayarkan')
                        <span style="color: green; font-weight: bold;">
                            {{ $p->status }}
                        </span>
                    @elseif($p->status == 'Ditangguhkan')
                        <span style="color: orange; font-weight: bold;">
                            {{ $p->status }}
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

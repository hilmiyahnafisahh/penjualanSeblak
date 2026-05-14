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
                <th>Periode</th>
                <th>Tanggal</th>
                <th>jam kerja</th>
                <th>upah per jam</th>
                <th>kehadiran</th>
                <th>Gaji</th>
                <th>status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penggajian as $p)
            <tr>
                <td>{{ $p->karyawan->nama }}</td>
                <td>{{ $p->periode }}</td>
                <td>{{ $p->tanggal_penggajian }}</td>
                <td>{{ $p->jam_kerja }}</td>
                <td>{{ rupiah($p->upah_per_jam) }}</td>
                <td>{{ $p->kehadiran }}</td>
                <td class="text-right">{{ rupiah($p->nominal) }}</td>
                <td>{{ $p->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

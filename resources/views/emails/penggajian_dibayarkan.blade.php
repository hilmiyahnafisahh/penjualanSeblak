<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Penggajian Berhasil Dibayarkan</title>
</head>
<body>
    <h1>Penggajian Berhasil Dibayarkan</h1>
    <p>Penggajian dengan ID <strong>{{ $penggajian->id_penggajian }}</strong> telah dibayarkan.</p>
    <p>Karyawan: <strong>{{ $penggajian->karyawan->nama ?? 'Tidak Diketahui' }}</strong></p>
    <p>Periode: <strong>{{ $penggajian->periode }}</strong></p>
    <p>Total Gaji: <strong>Rp {{ number_format($penggajian->nominal, 0, ',', '.') }}</strong></p>
    <p>Status sekarang: <strong>{{ $penggajian->status }}</strong></p>
    <p>Terima kasih telah menggunakan sistem payroll.</p>
</body>
</html>

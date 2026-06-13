<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Courier New', monospace;
      font-size: 11px;
      color: #111;
      width: 226px;
      padding: 10px;
      margin: auto;
    }
    .center { text-align: center; }
    .bold   { font-weight: bold; }
    .divider-solid  { border-top: 1px solid #000; margin: 6px 0; } 
    .divider-dashed { border-top: 1px dashed #555; margin: 6px 0; }
    .row { display: flex; justify-content: space-between; margin: 3px 0; } //
    .row .label { 
      color: #444;
      width: 110px; } 
    .row .value {
      text-align: right;
    }
    .total-box {
      background: #111;
      color: #fff;
      text-align: center;
      padding: 8px 4px;
      border-radius: 4px;
      margin: 8px 0;
    } 
    .total-box .amount { font-size: 16px; font-weight: bold; } 
    .badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: bold;
      border: 1px solid #000;
    }
    .footer { text-align: center; font-size: 9px; color: #777; margin-top: 8px; }
  </style>
</head>
<body>

  <div class="center bold" style="font-size:13px;">SEBLAK HUHA</div>
  <div class="center" style="font-size:9px; color:#555;">Slip Gaji Karyawan</div>
  <div class="divider-solid"></div>

  <div class="center bold">{{ $penggajian->id_penggajian }}</div>
  <div class="divider-dashed"></div>

  <div class="divider-dashed"></div>
  <div class="bold" style="margin-bottom:4px;">Data Karyawan</div>
  <div class="row">
    <span class="label">Karyawan  :</span>
    <span class="value bold">{{ $penggajian->karyawan->nama ?? '-' }}</span>
  </div>
  <div class="row">
    <span class="label">ID Karyawan :</span>
    <span class="value">{{ $penggajian->id_karyawan }}</span>
  </div>
  <div class="row">
    <span class="label">Periode :</span>
    <span class="value">{{ $penggajian->periode }}</span>
  </div>
  <div class="row">
    <span class="label">Tanggal :</span>
    <span class="value">{{ \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d/m/Y') }}</span>
  </div>

  <div class="divider-dashed"></div>
  <div class="bold" style="margin-bottom:4px;">Rincian Perhitungan</div>

  <div class="row">
    <span class="label">Jam Kerja/Hari  :</span>
    <span class="value">{{ $penggajian->jam_kerja }} jam</span>
  </div>
  <div class="row">
    <span class="label">Upah/Jam  :</span>
    <span class="value">Rp {{ number_format($penggajian->upah_per_jam, 0, ',', '.') }}</span>
  </div>
  <div class="row">
    <span class="label">Gaji/Hari :</span>
    <span class="value">Rp {{ number_format($penggajian->gaji_per_hari, 0, ',', '.') }}</span>
  </div>
  <div class="row">
    <span class="label">Kehadiran  :</span>
    <span class="value">{{ $penggajian->kehadiran }} hari</span>
  </div>

  <div class="divider-dashed"></div>
  <div class="center" style="font-size:9px; color:#555; margin-bottom:4px;">
    {{ $penggajian->jam_kerja }} jam × Rp {{ number_format($penggajian->upah_per_jam, 0, ',', '.') }}
    × {{ $penggajian->kehadiran }} hari
  </div>

  <div class="total-box">
    <div style="font-size:9px; opacity:.8;">TOTAL GAJI DITERIMA</div>
    <div class="amount">Rp {{ number_format($penggajian->nominal, 0, ',', '.') }}</div>
  </div>

  <div class="center">
    <span class="badge">{{ strtoupper($penggajian->status) }}</span>
  </div>

  <div class="divider-solid"></div>
  <div class="footer">
    Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB<br>
    Seblak Nusantara — Sistem Penggajian
  </div>

</body>
</html>

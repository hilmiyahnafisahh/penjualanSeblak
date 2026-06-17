<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #111; padding: 20px; }
    h1 { font-size: 16px; text-align: center; margin-bottom: 4px; }
    .subtitle { text-align: center; color: #555; margin-bottom: 16px; font-size: 11px; }
    .akun-block { margin-bottom: 18px; }
    .akun-header { background: #1e40af; color: #fff; padding: 6px 10px; font-weight: bold; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #f1f5f9; padding: 5px 8px; border: 1px solid #cbd5e1; text-align: left; font-size: 10px; }
    td { padding: 4px 8px; border: 1px solid #e2e8f0; }
    .text-right { text-align: right; }
    tfoot td { background: #eff6ff; font-weight: bold; color: #1e40af; }
    .page-footer { text-align: center; font-size: 9px; color: #888; margin-top: 20px; }
  </style>
</head>
<body>
  <h1>Buku Besar</h1>
  <div class="subtitle">Seblak Sangkuriang — Dicetak {{ now()->format('d/m/Y H:i') }}</div>

  @foreach($groupedDetails as $akunId => $details)
    @php
      $akun        = $details->first()->akun;
      $totalDebit  = $details->sum('debit');
      $totalCredit = $details->sum('credit');
    @endphp
    <div class="akun-block">
      <div class="akun-header">{{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? 'Tanpa Akun' }}</div>
      <table>
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>ID</th>
            <th>Ref</th>
            <th>Deskripsi</th>
            <th class="text-right">Debet</th>
            <th class="text-right">Kredit</th>
          </tr>
        </thead>
        <tbody>
          @foreach($details as $d)
          <tr>
            <td>{{ optional($d->jurnal)->tgl ? \Carbon\Carbon::parse($d->jurnal->tgl)->format('d/m/Y') : '-' }}</td>
            <td>{{ optional($d->jurnal)->id ?? '-' }}</td>
            <td>{{ optional($d->jurnal)->no_referensi ?? '-' }}</td>
            <td>{{ $d->deskripsi ?? optional($d->jurnal)->deskripsi ?? '-' }}</td>
            <td class="text-right">{{ $d->debit ? 'Rp '.number_format($d->debit,0,',','.') : '' }}</td>
            <td class="text-right">{{ $d->credit ? 'Rp '.number_format($d->credit,0,',','.') : '' }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="text-right">Total {{ $akun->kode_akun ?? '' }}</td>
            <td class="text-right">Rp {{ number_format($totalDebit,0,',','.') }}</td>
            <td class="text-right">Rp {{ number_format($totalCredit,0,',','.') }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  @endforeach

  <div class="page-footer">Seblak Sangkuriang — Sistem Akuntansi</div>
</body>
</html>

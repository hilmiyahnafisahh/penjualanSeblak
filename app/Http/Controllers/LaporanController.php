<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function labaRugi()
    {
        // Periode dari query string (format YYYY-MM) atau bulan berjalan
        $periode = request('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        // Kelompokkan per akun pendapatan (jenis_akun = 'Pendapatan') dan jumlahkan credit
        $pendapatanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('credit'),
                ];
            })->values();

        // Kelompokkan per akun beban (jenis_akun = 'Beban') dan jumlahkan debit
        $bebanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('debit'),
                ];
            })->values();

        $totalPendapatan = $pendapatanGroups->sum('jumlah');
        $totalBeban = $bebanGroups->sum('jumlah');
        $labaBersih = $totalPendapatan - $totalBeban;

        return view('laporan.laba-rugi', [
            'periode' => $periode,
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'pendapatanGroups' => $pendapatanGroups,
            'bebanGroups' => $bebanGroups,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'labaBersih' => $labaBersih,
        ]);
    }

    public function pdf()
    {
        $periode = request('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $pendapatanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('credit'),
                ];
            })->values();

        $bebanGroups = $details->filter(fn($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
            ->groupBy(fn($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode' => $akun->kode_akun ?? '-',
                    'nama' => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('debit'),
                ];
            })->values();

        $totalPendapatan = $pendapatanGroups->sum('jumlah');
        $totalBeban = $bebanGroups->sum('jumlah');
        $labaBersih = $totalPendapatan - $totalBeban;

        $data = [
            'periode' => $periode,
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'pendapatanGroups' => $pendapatanGroups,
            'bebanGroups' => $bebanGroups,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'labaBersih' => $labaBersih,
        ];

        $pdf = Pdf::loadView('laporan.laba-rugi-pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download('laba-rugi-' . ($periode ?? now()->format('Y-m')) . '.pdf');
    }

    public function jurnalPdf(Request $request)
    {
        $periode = $request->query('periode');
        $start = $periode ? Carbon::createFromFormat('Y-m', $periode)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $periode ? Carbon::createFromFormat('Y-m', $periode)->endOfMonth() : Carbon::now()->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $jurnals = $details->groupBy(fn($d) => $d->jurnal_id)->map(function ($items) {
            return $items->first()->jurnal->load('jurnaldetail.akun');
        })->values();

        $data = [
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'jurnals' => $jurnals,
        ];

        $pdf = Pdf::loadView('pdf.jurnal-umum-pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download('jurnal-umum-' . ($periode ?? now()->format('Y-m')) . '.pdf');
    }
}

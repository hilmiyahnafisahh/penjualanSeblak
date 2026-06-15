<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\JurnalDetail;
use Carbon\Carbon;

class LaporanLabaRugi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laba Rugi';
    protected static string $view = 'filament.pages.laporan-laba-rugi';

    public function getViewData(): array
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

        return [
            'periode' => $periode,
            'periodeLabel' => $periode ? Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') : Carbon::now()->translatedFormat('F Y'),
            'pendapatanGroups' => $pendapatanGroups,
            'bebanGroups' => $bebanGroups,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'labaBersih' => $labaBersih,
        ];
    }
}

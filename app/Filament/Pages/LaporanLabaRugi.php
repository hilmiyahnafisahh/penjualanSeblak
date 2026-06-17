<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\JurnalDetail;
use Carbon\Carbon;

class LaporanLabaRugi extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laba Rugi';
    protected static string  $view            = 'filament.pages.laporan-laba-rugi';

    // Livewire public property — berubah saat user pilih bulan
    public string $periode = '';

    public function mount(): void
    {
        $this->periode = now()->format('Y-m');
    }

    // Dipanggil oleh wire:click="filter" — cukup kosong,
    // karena Livewire akan re-render dan getViewData() terpanggil ulang
    public function filter(): void
    {
        $data = $this->getViewData();

        $isEmpty = $data['pendapatanGroups']->isEmpty() && $data['bebanGroups']->isEmpty();

        if ($isEmpty) {
            \Filament\Notifications\Notification::make()
                ->title('Tidak ada data')
                ->body('Tidak ada transaksi jurnal untuk periode ' . \Carbon\Carbon::createFromFormat('Y-m', $this->periode)->translatedFormat('F Y') . '.')
                ->warning()
                ->send();
        }
    }

    public function getViewData(): array
    {
        $periode = $this->periode ?: now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $pendapatanGroups = $details
            ->filter(fn ($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
            ->groupBy(fn ($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode'   => $akun->kode_akun ?? '-',
                    'nama'   => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('credit'),
                ];
            })->values();

        $bebanGroups = $details
            ->filter(fn ($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
            ->groupBy(fn ($d) => $d->akun->id)
            ->map(function ($items) {
                $akun = $items->first()->akun;
                return [
                    'kode'   => $akun->kode_akun ?? '-',
                    'nama'   => $akun->nama_akun ?? '-',
                    'jumlah' => $items->sum('debit'),
                ];
            })->values();

        $totalPendapatan = $pendapatanGroups->sum('jumlah');
        $totalBeban      = $bebanGroups->sum('jumlah');
        $labaBersih      = $totalPendapatan - $totalBeban;
        $hasData         = $pendapatanGroups->isNotEmpty() || $bebanGroups->isNotEmpty();

        return compact(
            'periode',
            'pendapatanGroups',
            'bebanGroups',
            'totalPendapatan',
            'totalBeban',
            'labaBersih',
            'hasData'
        );
    }
}

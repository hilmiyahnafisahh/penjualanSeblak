<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\JurnalDetail;
use Carbon\Carbon;

class GrafikLabaRugi extends ChartWidget
{
    protected static ?string $heading   = 'Grafik Laba Rugi';
    protected static ?int    $sort      = 3;
    protected int | string | array $columnSpan = 'full';

    // Pilihan tahun — bisa diubah user via filter
    public ?string $filter = '2026';

    protected function getFilters(): ?array 
    {
        $options = [];
        for ($y = 2030; $y >= 2020; $y--) {
            $options[(string) $y] = (string) $y;
        }
        return $options;
    }

    protected function getData(): array 
    {
        $tahun = $this->filter ?? now()->year;

        $labels     = []; //untuk menyimpan nama bulan
        $pendapatan = [];
        $beban      = [];
        $labaBersih = [];

        // Loop Jan–Des tahun yang dipilih
        for ($m = 1; $m <= 12; $m++) {
            $bulan = Carbon::createFromDate($tahun, $m, 1);
            $start = $bulan->copy()->startOfMonth();
            $end   = $bulan->copy()->endOfMonth();

            $details = JurnalDetail::with('akun') 
                ->whereHas('jurnal', fn ($q) => $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]))
                ->get();

            $totalPendapatan = $details
                ->filter(fn ($d) => $d->akun && $d->akun->jenis_akun === 'Pendapatan')
                ->sum('credit');

            $totalBeban = $details
                ->filter(fn ($d) => $d->akun && $d->akun->jenis_akun === 'Beban')
                ->sum('debit');

            $labels[]     = $bulan->translatedFormat('M');
            $pendapatan[] = (float) $totalPendapatan;
            $beban[]      = (float) $totalBeban;
            $labaBersih[] = (float) ($totalPendapatan - $totalBeban);
        }

        return [
            'datasets' => [
                [
                    'label'            => 'Pendapatan',
                    'data'             => $pendapatan,
                    'borderColor'      => '#16a34a',
                    'backgroundColor'  => '#16a34a',
                    'fill'             => false,
                    'tension'          => 0,
                    'pointRadius'      => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth'      => 2,
                ],
                [
                    'label'            => 'Beban',
                    'data'             => $beban,
                    'borderColor'      => '#dc2626',
                    'backgroundColor'  => '#dc2626',
                    'fill'             => false,
                    'tension'          => 0,
                    'pointRadius'      => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth'      => 2,
                ],
                [
                    'label'            => 'Laba Bersih',
                    'data'             => $labaBersih,
                    'borderColor'      => '#2563eb',
                    'backgroundColor'  => '#2563eb',
                    'fill'             => false,
                    'tension'          => 0,
                    'pointRadius'      => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth'      => 2,
                    'borderDash'       => [6, 3],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'function(v) { return \'Rp \' + v.toLocaleString(\'id-ID\'); }',
                    ],
                ],
            ],
        ];
    }
}

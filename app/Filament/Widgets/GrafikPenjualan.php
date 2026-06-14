<?php

namespace App\Filament\Widgets;

use App\Models\Pemesanan;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GrafikPenjualan extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Grafik Penjualan';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'harian';

    protected function getFilters(): ?array
    {
        return [
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        if ($this->filter === 'harian') {

            for ($i = 6; $i >= 0; $i--) {

                $tanggal = Carbon::now()->subDays($i);

                $labels[] = $tanggal->format('d M');

                $data[] = Pemesanan::whereDate(
                    'tanggal_pemesanan',
                    $tanggal->toDateString()
                )->count();
            }

        } elseif ($this->filter === 'mingguan') {

            for ($i = 7; $i >= 0; $i--) {

                $awal = Carbon::now()->subWeeks($i)->startOfWeek();
                $akhir = Carbon::now()->subWeeks($i)->endOfWeek();

                $labels[] = 'Minggu ' . (8 - $i);

                $data[] = Pemesanan::whereBetween(
                    'tanggal_pemesanan',
                    [$awal, $akhir]
                )->count();
            }

        } else {

            for ($i = 11; $i >= 0; $i--) {

                $bulan = Carbon::now()->subMonths($i);

                $labels[] = $bulan->translatedFormat('M Y');

                $data[] = Pemesanan::whereMonth(
                    'tanggal_pemesanan',
                    $bulan->month
                )
                ->whereYear(
                    'tanggal_pemesanan',
                    $bulan->year
                )
                ->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pemesanan',
                    'data' => $data,

                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.2)',

                    'fill' => true,
                    'tension' => 0.4,
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
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'max' => 10,
                'ticks' => [
                    'stepSize' => 1,
                    'precision' => 0,
                    'autoSkip' => false,
                ],
            ],
        ],
    ];
}
}
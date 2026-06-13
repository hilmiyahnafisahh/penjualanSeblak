<?php

namespace App\Filament\Widgets;

use App\Models\CatatBeban;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GrafikBeban extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Grafik Beban Bulanan';

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {

            $bulan = Carbon::now()->subMonths($i);

            $labels[] = $bulan->translatedFormat('M Y');

            $data[] = CatatBeban::whereMonth(
                'tanggal',
                $bulan->month
            )
            ->whereYear(
                'tanggal',
                $bulan->year
            )
            ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Beban',
                    'data' => $data,

                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderWidth' => 2,
                ],
            ],

            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                ],
            ],
        ];
    }
}
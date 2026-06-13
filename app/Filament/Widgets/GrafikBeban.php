<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\CatatBeban;
use Carbon\Carbon;

class GrafikBeban extends ChartWidget
{
    protected static ?string $heading = 'Grafik Beban Bulanan';

    protected function getData(): array
    {
        $bulan = [];
        $total = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i);

            $bulan[] = $date->translatedFormat('M');

            $total[] = CatatBeban::whereMonth(
                'tanggal',
                $date->month
            )->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Beban',
                    'data' => $total,
                ],
            ],
            'labels' => $bulan,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
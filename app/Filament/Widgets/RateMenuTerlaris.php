<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\ChartWidget;

class RateMenuTerlaris extends ChartWidget
{
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Menu Terlaris';

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $data = Barang::query()
            ->select('nama_barang')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Menu Terlaris',

                    'data' => array_fill(0, $data->count(), 1),

                    // 🎨 samakan warna dengan beban (nanti kita samakan juga di beban)
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderWidth' => 2,
                ],
            ],

            'labels' => $data->pluck('nama_barang')->toArray(),
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
                'max' => 5,
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
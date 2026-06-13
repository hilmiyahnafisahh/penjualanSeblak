<?php

namespace App\Filament\Widgets;

use App\Models\Pemesanan;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GrafikPenjualan extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {

            $tanggal = Carbon::now()->subDays($i);

            $labels[] = $tanggal->format('d M');

            $data[] = Pemesanan::whereDate(
                'created_at',
                $tanggal->toDateString()
            )->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\CatatBeban;
use App\Models\Pesanan;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Barang',
                Barang::count()
            )
                ->description('Barang tersedia')
                ->color('success')
                ->icon('heroicon-o-cube'),

            Stat::make(
                'Total Pelanggan',
                Pelanggan::count()
            )
                ->description('Pelanggan terdaftar')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total Pesanan',
                Pesanan::count()
            )
                ->description('Pesanan masuk')
                ->color('warning')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make(
                'Total Beban',
                CatatBeban::count()
            )
                ->description('Catatan beban')
                ->color('danger')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\CatatBeban;
use App\Models\Pemesanan;
use App\Models\Pembayaran;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            // TOTAL PENDAPATAN
            Stat::make(
                'Total Pendapatan',
                'Rp ' . number_format(
                    Pembayaran::sum('total_pembayaran') ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
                ->description('Total pembayaran berhasil')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([5, 10, 8, 12, 15, 18, 20]),

            // TOTAL BARANG HAMPIR HABIS
            Stat::make(
                'Barang Hampir Habis',
                Barang::where('stok', '<=', 5)->count()
            )
                ->description('Stok kurang dari atau sama dengan 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([12, 10, 8, 7, 6, 4, 3]),

            // TOTAL PELANGGAN
            Stat::make(
                'Total Pelanggan',
                Pelanggan::count()
            )
                ->description('Pelanggan terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([2, 4, 6, 5, 7, 9, 11]),

            // TOTAL PEMESANAN
            Stat::make(
                'Total Pemesanan',
                Pemesanan::count()
            )
                ->description('Transaksi masuk')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning')
                ->chart([1, 3, 2, 4, 6, 5, 7]),

            // TOTAL BEBAN
            Stat::make(
                'Total Beban',
                CatatBeban::count()
            )
                ->description('Catatan pengeluaran')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray')
                ->chart([1, 2, 3, 2, 4, 5, 6]),
        ];
    }
}
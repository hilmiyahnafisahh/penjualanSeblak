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

            Stat::make(
                'Total Barang',
                Barang::count()
            )
                ->description('Barang tersedia')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success')
                ->chart([3, 5, 7, 6, 8, 10, 12]),

            Stat::make(
                'Total Pelanggan',
                Pelanggan::count()
            )
                ->description('Pelanggan terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([2, 4, 6, 5, 7, 9, 11]),

            Stat::make(
                'Total Pemesanan',
                Pemesanan::count()
            )
                ->description('Transaksi masuk')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning')
                ->chart([1, 3, 2, 4, 6, 5, 7]),

            Stat::make(
                'Total Beban',
                CatatBeban::count()
            )
                ->description('Catatan pengeluaran')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger')
                ->chart([1, 2, 3, 2, 4, 5, 6]),

            // 💰 TOTAL PENDAPATAN (SUDAH BENAR SESUAI DATABASE KAMU)
            Stat::make(
                'Total Pendapatan',
                'Rp ' . number_format(
                    Pembayaran::sum('total_pembayaran') ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
                ->description('Total dari pembayaran berhasil')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([5, 10, 8, 12, 15, 18, 20]),
        ];
    }
}
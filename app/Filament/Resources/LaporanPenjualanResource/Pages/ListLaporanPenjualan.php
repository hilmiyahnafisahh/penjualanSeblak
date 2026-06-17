<?php

namespace App\Filament\Resources\LaporanPenjualanResource\Pages;

use App\Filament\Resources\LaporanPenjualanResource;
use App\Services\LaporanPenjualanAiService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLaporanPenjualan extends ListRecords
{
    protected static string $resource = LaporanPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateAi')
                ->label('Generate laporan & analisa dengan AI')
                ->icon('heroicon-m-sparkles')
                ->color('warning')
                ->modalHeading('Generate Laporan Penjualan + Analisa AI')
                ->modalDescription('Sistem akan mengolah data penjualan lalu meminta Gemini menganalisisnya.')
                ->form([
                    Forms\Components\Select::make('tipe_periode')
                        ->label('Tipe Periode')
                        ->options([
                            'daily'   => 'Harian',
                            'weekly'  => 'Mingguan',
                            'monthly' => 'Bulanan',
                        ])
                        ->default('monthly')
                        ->required()
                        ->reactive(),

                    Forms\Components\DatePicker::make('periode_daily')
                        ->label('Pilih Tanggal')
                        ->default(now()->format('Y-m-d'))
                        ->visible(fn ($get) => $get('tipe_periode') === 'daily'),

                    Forms\Components\TextInput::make('periode_weekly')
                        ->label('Minggu (format: 2026-W24)')
                        ->default(now()->isoFormat('GGGG-[W]WW'))
                        ->placeholder('contoh: 2026-W24')
                        ->visible(fn ($get) => $get('tipe_periode') === 'weekly'),

                    Forms\Components\TextInput::make('periode_monthly')
                        ->label('Bulan (YYYY-MM)')
                        ->default(now()->format('Y-m'))
                        ->placeholder('contoh: 2026-06')
                        ->visible(fn ($get) => $get('tipe_periode') === 'monthly'),
                ])
                ->action(function (array $data) {
                    $type = $data['tipe_periode'];
                    $value = match ($type) {
                        'daily'   => $data['periode_daily'] ?? now()->format('Y-m-d'),
                        'weekly'  => $data['periode_weekly'] ?? now()->isoFormat('GGGG-[W]WW'),
                        'monthly' => $data['periode_monthly'] ?? now()->format('Y-m'),
                        default   => now()->format('Y-m'),
                    };

                    try {
                        $laporan = app(LaporanPenjualanAiService::class)->analisa($type, $value);

                        Notification::make()
                            ->title('Laporan & analisa AI berhasil dibuat!')
                            ->success()
                            ->send();

                        $this->redirect(LaporanPenjualanResource::getUrl('view', ['record' => $laporan]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal generate laporan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

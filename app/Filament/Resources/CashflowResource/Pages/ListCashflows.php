<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use App\Services\CashflowAi;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCashflow extends ListRecords
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('analisaAi')
                ->label('Generate arus kas & analisa dengan AI')
                ->icon('heroicon-m-sparkles')
                ->color('warning')
                ->modalHeading('Analisa Arus Kas dengan AI')
                ->modalDescription('Sistem akan menghitung kas masuk & keluar lalu meminta Gemini menganalisanya.')
                ->form([
                    Forms\Components\TextInput::make('periode')
                        ->label('Periode (YYYY-MM)')
                        ->default(now()->format('Y-m'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $insight = app(CashflowAi::class)->analisa($data['periode']);

                        Notification::make()
                            ->title('Analisa arus kas berhasil dibuat')
                            ->success()
                            ->send();

                        // langsung buka halaman detail (hasil AI tampil di halaman)
                        $this->redirect(CashflowResource::getUrl('view', ['record' => $insight]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menganalisa')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
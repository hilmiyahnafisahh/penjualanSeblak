<?php

namespace App\Filament\Resources\PenggajianResource\Pages;

use App\Filament\Resources\PenggajianResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewPenggajian extends ViewRecord
{
    protected static string $resource = PenggajianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(fn () => PenggajianResource::getUrl('edit', ['record' => $this->record])),

            Action::make('cetak_pdf')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    $penggajian = $this->record;
                    $penggajian->load('karyawan');

                    $pdf = Pdf::loadView('pdf.struk-penggajian', compact('penggajian'))
                        ->setPaper([0, 0, 226.77, 600], 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'struk-gaji-' . $penggajian->id_penggajian . '.pdf'
                    );
                }),
        ];
    }

    // Override view agar tampil sebagai struk
    public function getView(): string
    {
        return 'filament.resources.penggajian.view-struk';
    }
}

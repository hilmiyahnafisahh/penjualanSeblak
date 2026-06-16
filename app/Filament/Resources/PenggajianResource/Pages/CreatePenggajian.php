<?php

namespace App\Filament\Resources\PenggajianResource\Pages;

use App\Filament\Resources\PenggajianResource;
use App\Models\Penggajian;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePenggajian extends CreateRecord
{
    protected static string $resource = PenggajianResource::class;

    /**
     * Validasi sebelum record disimpan:
     * 1 karyawan hanya boleh 1x penggajian per periode.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Penggajian::sudahDigaji($data['id_karyawan'], $data['periode'])) {
            Notification::make()
                ->title('Penggajian sudah ada')
                ->body("Karyawan ini sudah memiliki data penggajian untuk periode {$data['periode']}.")
                ->danger()
                ->persistent()
                ->send();

            $this->halt(); // hentikan proses simpan
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

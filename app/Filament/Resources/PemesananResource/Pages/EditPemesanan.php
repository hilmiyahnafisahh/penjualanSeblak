<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use Filament\Resources\Pages\EditRecord;

class EditPemesanan extends EditRecord
{
    protected static string $resource = PemesananResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $total = 0;

        foreach ($data['DetailPesanan'] ?? [] as $item) {
            $total += $item['subtotal'] ?? 0;
        }

        $data['subtotal'] = $total;

        return $data;
    }
}
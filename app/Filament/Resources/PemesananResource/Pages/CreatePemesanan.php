<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use App\Models\Menu;
use Filament\Resources\Pages\CreateRecord;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    // Hitung ulang subtotal dari detail sebelum disimpan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        foreach ($data['DetailPesanan'] ?? [] as $item) {
            $total += $item['subtotal'] ?? 0;
        }

        $data['subtotal'] = $total;

        return $data;
    }
}
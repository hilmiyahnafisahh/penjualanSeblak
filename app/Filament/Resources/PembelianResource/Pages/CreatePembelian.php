<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    // ✅ Sembunyikan tombol bawah (Create & Cancel default)
    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getFormActions(): array
    {
        return []; // ✅ Kosongkan tombol bawah
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $items  = $this->data['barang'] ?? [];

        DB::transaction(function () use ($record, $items) {
            foreach ($items as $item) {
                if (!empty($item['id_barang']) && !empty($item['jumlah'])) {
                    $barang = Barang::where('id_barang', $item['id_barang'])->first();
                    if ($barang) {
                        $barang->increment('stok', (int) $item['jumlah']);
                    }
                }
            }

            $jumlahBayar = $record->pembayaran()->sum('jumlah_bayar');
            $record->update(['total_bayar' => $jumlahBayar]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
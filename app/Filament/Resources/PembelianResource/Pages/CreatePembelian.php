<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $items  = $this->data['barang'] ?? [];

        DB::transaction(function () use ($record, $items) {
            // ✅ Update stok barang
            foreach ($items as $item) {
                if (!empty($item['id_barang']) && !empty($item['jumlah'])) {
                    $barang = Barang::where('id_barang', $item['id_barang'])->first();
                    if ($barang) {
                        $barang->increment('stok', (int) $item['jumlah']);
                    }
                }
            }

            // ✅ Ambil jumlah_bayar dari relasi pembayaran yang sudah tersimpan
            $jumlahBayar = $record->pembayaran()->sum('jumlah_bayar');
            $record->update(['total_bayar' => $jumlahBayar]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
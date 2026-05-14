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
        $items = $this->data['barang'] ?? [];

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if (!empty($item['id_barang']) && !empty($item['jumlah'])) {
                    $barang = Barang::where('id_barang', $item['id_barang'])->first();
                    if ($barang) {
                        // Stok BERTAMBAH karena ini transaksi pembelian masuk
                        $barang->increment('stok', (int) $item['jumlah']);
                    }
                }
            }
        });

    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
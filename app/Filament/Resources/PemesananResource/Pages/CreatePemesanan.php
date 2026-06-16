<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use App\Models\Barang;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['id_pesanan'])) {
            $data['id_pesanan'] = \App\Models\Pemesanan::getKodeFaktur();
        }
        return $data;
    }

    /**
     * Biarkan Filament handle create + relasi,
     * lalu kurangi stok setelah record tersimpan.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Filament handle simpan pemesanan + DetailPesanan
            $record = parent::handleRecordCreation($data);

            // Kurangi stok topping
            $this->kurangiStokTopping($record);

            return $record;
        });
    }

    /**
     * Kurangi stok barang berdasarkan topping di setiap detail.
     */
    private function kurangiStokTopping(Model $pemesanan): void
    {
        $pemesanan->load('DetailPesanan');

        foreach ($pemesanan->DetailPesanan as $detail) {

            $toppings = $detail->topping ?? []; // sudah di-decode oleh cast array

            if (empty($toppings) || ! is_array($toppings)) {
                continue;
            }

            foreach ($toppings as $topping) {
                $idBarang = $topping['id_barang'] ?? null;
                $qty      = (int) ($topping['qty'] ?? 0);

                if (! $idBarang || $qty <= 0) {
                    continue;
                }

                $barang = Barang::where('id_barang', $idBarang)->lockForUpdate()->first();

                if (! $barang) {
                    continue;
                }

                if ($barang->stok_barang < $qty) {
                    Notification::make()
                        ->title('Stok Tidak Cukup')
                        ->body("Stok {$barang->nama_barang} hanya {$barang->stok_barang}, dibutuhkan {$qty}.")
                        ->danger()
                        ->persistent()
                        ->send();

                    // Batalkan seluruh transaction
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi.");
                }

                // Kurangi stok dengan cara aman (hindari race condition)
                $barang->decrement('stok_barang', $qty);
            }
        }

        Notification::make()
            ->title('Pemesanan Berhasil')
            ->body('Stok topping berhasil diperbarui.')
            ->success()
            ->send();
    }
}
<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use App\Models\Barang;
use Filament\Resources\Pages\EditRecord;

class EditPemesanan extends EditRecord
{
    protected static string $resource = PemesananResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $total = 0;

        foreach ($data['DetailPesanan'] ?? [] as $detailKey => $item) {
            $total += $item['subtotal'] ?? 0;

            if (!empty($item['topping']) && is_array($item['topping'])) {
                foreach ($item['topping'] as $toppingKey => $topping) {
                    $barang = Barang::where('id_barang', $topping['id_barang'] ?? null)->first();
                    $hargaTopping = (float) ($barang?->harga_barang ?? $topping['harga'] ?? 0);
                    $qtyTopping = (int) ($topping['qty'] ?? 1);
                    $subtotalTopping = $hargaTopping * $qtyTopping;

                    $data['DetailPesanan'][$detailKey]['topping'][$toppingKey]['harga'] = $hargaTopping;
                    $data['DetailPesanan'][$detailKey]['topping'][$toppingKey]['subtotal'] = $subtotalTopping;

                    $total += $subtotalTopping;
                }
            }
        }

        $data['subtotal'] = $total;

        return $data;
    }
}
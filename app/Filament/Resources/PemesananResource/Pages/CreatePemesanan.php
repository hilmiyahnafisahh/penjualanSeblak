<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Menu;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    protected bool $redirectToPayment = false;

    // hitung subtotal
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        foreach ($data['detail_pesanan'] ?? [] as $item) {
            $total += $item['subtotal'] ?? 0;

            // Pastikan harga dan subtotal topping terisi dari database
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
protected function afterCreate(): void
    {
        $detailItems = $this->data['DetailPesanan'] ?? [];
        $this->decreaseStockFromPesanan($detailItems);

        if ($this->redirectToPayment) {
            $this->redirectUrl = '/admin/pembayaran/create?id_pemesanan=' . $this->record->id;
        }
    }

    protected function decreaseStockFromPesanan(array $detailItems): void
    {
        DB::transaction(function () use ($detailItems) {
            foreach ($detailItems as $detail) {
                $toppingItems = $detail['topping'] ?? [];

                foreach ($toppingItems as $toppingItem) {
                    $barang = Barang::find($toppingItem['id_barang'] ?? null);
                    $qtyTopping = (int) ($toppingItem['qty'] ?? 0);
                    if ($barang && $qtyTopping > 0) {
                        $barang->decrement('stok', $qtyTopping);
                    }
                }
            }
        });
    }
    // redirect setelah create
    protected function getRedirectUrl(): string
    {
        if ($this->redirectToPayment && $this->record) {
            return route('pembayaran.show', ['id' => $this->record->id]);
        }

        return $this->getResource()::getUrl('index');
    }

    // tombol tambahan
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Pesanan'),

            Action::make('bayarSekarang')
                ->label('Bayar Sekarang')
                ->color('success')
                ->action('bayarSekarang'),

            $this->getCancelFormAction(),
        ];
    }

    public function bayarSekarang(): void
    {
        $this->redirectToPayment = true;

        $this->create();
    }
}
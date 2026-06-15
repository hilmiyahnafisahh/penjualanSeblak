<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use App\Models\Barang;
use App\Models\Menu;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    protected bool $redirectToPayment = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['id_pesanan'])) {
            $data['id_pesanan'] = \App\Models\Pemesanan::getKodeFaktur();
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $detailItems = $this->data['DetailPesanan'] ?? [];
        $this->kurangiStokTopping($detailItems);
    }

    private function kurangiStokTopping(array $detailItems): void
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

    protected function getRedirectUrl(): string
    {
        if ($this->redirectToPayment && $this->record) {
            return route('pembayaran.show', ['id' => $this->record->id]);
        }

        return $this->getResource()::getUrl('index');
    }

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
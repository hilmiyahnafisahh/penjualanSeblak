<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

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
        }

        $data['subtotal'] = $total;

        return $data;
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
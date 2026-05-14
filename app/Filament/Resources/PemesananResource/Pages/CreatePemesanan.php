<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use App\Models\Menu;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    protected ?string $redirectUrl = null;
    protected bool $redirectToPayment = false;

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

    protected function afterCreate(): void
    {
        if ($this->redirectToPayment) {
            $this->redirectUrl = '/admin/pembayaran/create?id_pemesanan=' . $this->record->id;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirectUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Simpan Pesanan')
                ->submit('create'),
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

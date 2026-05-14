<?php

namespace App\Filament\Resources\PembayaranResource\Pages;

use App\Filament\Resources\PembayaranResource;
use App\Models\Pembayaran;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    protected ?string $redirectUrl = null;

    public function mount(): void
    {
        parent::mount();

        // Cek jika ada parameter id_pemesanan di URL
        $idPemesanan = request()->query('id_pemesanan');
        if ($idPemesanan) {
            // Ambil data pemesanan
            $pemesanan = \App\Models\Pemesanan::find($idPemesanan);
            
            if ($pemesanan) {
                // Isi form dengan data pemesanan
                $this->form->fill([
                    'id_pemesanan' => $idPemesanan,
                    'total_pembayaran' => $pemesanan->subtotal,
                ]);
            }
        }
    }

    protected function afterCreate(): void
    {
        parent::afterCreate();

        $pembayaran = $this->record;

        if (in_array($pembayaran->metode_pembayaran, ['qris', 'transfer'])) {
            $pembayaran->status_pembayaran = 'pending';
            $pembayaran->save();
            $this->redirectUrl = route('pembayaran.midtrans', ['id' => $pembayaran->id]);

            return;
        }

        $pembayaran->status_pembayaran = 'lunas';
        $pembayaran->save();

        if ($pembayaran->pemesanan) {
            $pembayaran->pemesanan->update(['status_pemesanan' => 'selesai']);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirectUrl ?? $this->getResource()::getUrl('index');
    }
}

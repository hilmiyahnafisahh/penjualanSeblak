<?php

namespace App\Filament\Resources\PembayaranResource\Pages;

use App\Filament\Resources\PembayaranResource;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    // Gunakan nama properti berbeda agar tidak conflict
    protected ?string $customRedirectUrl = null;

    public function mount(): void
    {
        parent::mount();

        $idPemesanan = request()->query('id_pemesanan');
        if ($idPemesanan) {
            $pemesanan = Pemesanan::find($idPemesanan);
            
            if ($pemesanan) {
                $this->form->fill([
                    'id_pemesanan' => $idPemesanan,
                    // Pastikan key ini 'total_pembayaran' sesuai kolom database Anda
                    'total_pembayaran' => $pemesanan->subtotal,
                ]);
            }
        }
    }

    protected function afterCreate(): void
    {
        // JANGAN gunakan parent::afterCreate(); karena memicu BadMethodCallException

        $pembayaran = $this->record;

        if (in_array($pembayaran->metode_pembayaran, ['qris', 'transfer'])) {
            $pembayaran->status_pembayaran = 'pending';
            $pembayaran->save();
            
            $this->customRedirectUrl = route('pembayaran.midtrans', ['id' => $pembayaran->id]);
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
        // Mengarahkan ke Midtrans jika ada, jika tidak ke index
        return $this->customRedirectUrl ?? $this->getResource()::getUrl('index');
    }
}
<?php

namespace App\Filament\Resources\PembayaranResource\Pages;

use App\Filament\Resources\PembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

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
}

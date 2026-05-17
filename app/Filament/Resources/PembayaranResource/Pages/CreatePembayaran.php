<?php

namespace App\Filament\Resources\PembayaranResource\Pages;

use App\Filament\Resources\PembayaranResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    protected ?string $redirectUrl = null;

    public function mount(): void
    {
        parent::mount();

        // cek parameter dari URL
        $idPemesanan = request()->query('id_pemesanan');

        if ($idPemesanan) {

            $pemesanan = \App\Models\Pemesanan::find($idPemesanan);

            if ($pemesanan) {

                $this->form->fill([
                    'id_pemesanan'      => $idPemesanan,
                    'total_pembayaran'  => $pemesanan->subtotal,
                    'tanggal_pembayaran'=> now('Asia/Jakarta'),
                ]);
            }
        }
    }

    protected function afterCreate(): void
    {
        $pembayaran = $this->record;

        // jika qris / transfer
        if (in_array($pembayaran->metode_pembayaran, ['qris', 'transfer'])) {

            $pembayaran->status_pembayaran = 'pending';
            $pembayaran->save();

            if (config('services.midtrans.server_key')) {
                $this->redirectUrl = route(
                    'pembayaran.midtrans',
                    ['id' => $pembayaran->id]
                );

                return;
            }

            session()->flash('warning', 'Midtrans belum dikonfigurasi. Pembayaran disimpan sebagai pending.');
            return;
        }

        // jika cash
        $pembayaran->status_pembayaran = 'lunas';
        $pembayaran->save();

        // update status pesanan
        if ($pembayaran->pemesanan) {

            $pembayaran->pemesanan->update([
                'status_pemesanan' => 'selesai'
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirectUrl
            ?? $this->getResource()::getUrl('index');
    }
}
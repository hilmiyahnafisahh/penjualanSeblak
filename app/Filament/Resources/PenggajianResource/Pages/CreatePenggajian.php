<?php

namespace App\Filament\Resources\PenggajianResource\Pages;

use App\Filament\Resources\PenggajianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePenggajian extends CreateRecord
{
    protected static string $resource = PenggajianResource::class;

    protected ?string $redirectUrl = null;

    protected function afterCreate(): void
    {
        $penggajian = $this->record;

        if ($penggajian->status === 'Dibayarkan') {
            $penggajian->status = 'Ditangguhkan';
            $penggajian->save();

            if (config('services.midtrans.server_key')) {
                $this->redirectUrl = route('penggajian.midtrans', ['id' => $penggajian->id]);

                return;
            }

            session()->flash('warning', 'Midtrans belum dikonfigurasi. Data penggajian tersimpan sebagai pending.');
            return;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirectUrl ?? $this->getResource()::getUrl('index');
    }
}

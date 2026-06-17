<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditKaryawan extends EditRecord
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Setelah karyawan diupdate, sync user login-nya di tabel users.
     */
    protected function afterSave(): void
    {
        $karyawan = $this->record;
        $data     = $this->form->getRawState();

        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$email) return;

        $userGroup = in_array($karyawan->jabatan, ['kasir', 'koki', 'admin', 'owner'])
            ? $karyawan->jabatan
            : 'kasir';

        $updateData = [
            'name'       => $karyawan->nama,
            'user_group' => $userGroup,
        ];

        // Hanya update password kalau diisi
        if ($password) {
            $updateData['password'] = Hash::make($password);
        }

        User::updateOrCreate(
            ['email' => $email],
            $updateData
        );
    }
}

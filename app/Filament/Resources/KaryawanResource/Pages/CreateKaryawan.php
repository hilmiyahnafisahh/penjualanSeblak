<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateKaryawan extends CreateRecord
{
    protected static string $resource = KaryawanResource::class;

    /**
     * Setelah karyawan dibuat, buat/update user login-nya di tabel users.
     */
    protected function afterCreate(): void
    {
        $karyawan = $this->record;
        $data     = $this->form->getRawState();

        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$email) return;

        // Tentukan user_group berdasarkan jabatan
        $userGroup = in_array($karyawan->jabatan, ['kasir', 'koki', 'admin', 'owner'])
            ? $karyawan->jabatan
            : 'kasir';

        User::updateOrCreate(
            ['email' => $email],
            array_filter([
                'name'       => $karyawan->nama,
                'email'      => $email,
                'password'   => $password ? Hash::make($password) : null,
                'user_group' => $userGroup,
            ], fn ($v) => $v !== null)
        );
    }
}

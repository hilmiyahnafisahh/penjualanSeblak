<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {email?} {password?}';
    protected $description = 'Buat user admin untuk login';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@gmail.com';
        $password = $this->argument('password') ?? 'password';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
            ]
        );

        $this->info("✓ User berhasil dibuat/diupdate!");
        $this->info("Email: $email");
        $this->info("Password: $password");
    }
}

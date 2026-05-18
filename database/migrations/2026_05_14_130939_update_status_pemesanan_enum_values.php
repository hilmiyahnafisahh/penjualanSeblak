<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pemesanan MODIFY status_pemesanan ENUM('pending','belumdibayar','diproses','selesai') NOT NULL DEFAULT 'pending'");
        DB::statement("UPDATE pemesanan SET status_pemesanan = 'belumdibayar' WHERE status_pemesanan = 'pending'");
        DB::statement("ALTER TABLE pemesanan MODIFY status_pemesanan ENUM('belumdibayar','diproses','selesai') NOT NULL DEFAULT 'belumdibayar'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pemesanan MODIFY status_pemesanan ENUM('pending','diproses','selesai') NOT NULL DEFAULT 'pending'");
    }
};

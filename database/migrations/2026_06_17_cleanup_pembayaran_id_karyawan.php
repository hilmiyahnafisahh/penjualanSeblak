<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If pembayaran.id_karyawan exists but migration previously failed, drop it so the proper migration can run.
        $exists = DB::selectOne("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pembayaran' AND COLUMN_NAME = 'id_karyawan'");
        if ($exists && $exists->cnt > 0) {
            DB::statement("ALTER TABLE `pembayaran` DROP COLUMN `id_karyawan`");
        }
    }

    public function down(): void
    {
        // nothing to revert safely
    }
};

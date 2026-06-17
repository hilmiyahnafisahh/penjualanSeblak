<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure karyawan table uses InnoDB and utf8mb4_unicode_ci so foreign keys can be added
        DB::statement("ALTER TABLE `karyawan` ENGINE=InnoDB");
        DB::statement("ALTER TABLE `karyawan` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Ensure id_karyawan has an index (unique already creates one, but ensure existence)
        Schema::table('karyawan', function (Blueprint $table) {
            // If column exists and index missing, add unique index (no-op if already exists)
            // We cannot safely check index existence here without raw SQL, so attempt to create index name.
            try {
                $table->unique('id_karyawan', 'karyawan_id_karyawan_unique_temp');
            } catch (\Exception $e) {
                // ignore if index already exists
            }
        });
    }

    public function down(): void
    {
        // do not revert engine/charset changes automatically
    }
};

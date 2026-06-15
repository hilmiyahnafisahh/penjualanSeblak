<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_detail', function (Blueprint $table) {
            // Drop kolom coa_id kalau ada (tanpa drop foreign key)
            if (Schema::hasColumn('jurnal_detail', 'coa_id')) {
                $table->dropColumn('coa_id');
            }

            // Drop kolom kode_akun kalau ada
            if (Schema::hasColumn('jurnal_detail', 'kode_akun')) {
                $table->dropColumn('kode_akun');
            }

            // Tambah akun_id yang benar
            if (!Schema::hasColumn('jurnal_detail', 'akun_id')) {
                $table->foreignId('akun_id')
                    ->after('jurnal_id')
                    ->constrained('akun')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_detail', function (Blueprint $table) {
            if (Schema::hasColumn('jurnal_detail', 'akun_id')) {
                $table->dropForeign(['akun_id']);
                $table->dropColumn('akun_id');
            }
            $table->unsignedBigInteger('kode_akun');
        });
    }
};
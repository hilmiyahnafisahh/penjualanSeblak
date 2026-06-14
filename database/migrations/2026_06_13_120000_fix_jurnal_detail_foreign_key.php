<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jurnal_detail', function (Blueprint $table) {
            // Drop the incorrect foreign key
            $table->dropForeign(['kode_akun']);
            
            // Drop the kode_akun column
            $table->dropColumn('kode_akun');
        });

        Schema::table('jurnal_detail', function (Blueprint $table) {
            // Add proper foreign key to akun.id
            $table->foreignId('akun_id')
                ->after('jurnal_id')
                ->constrained('akun')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_detail', function (Blueprint $table) {
            $table->dropForeign(['akun_id']);
            $table->dropColumn('akun_id');
        });

        Schema::table('jurnal_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('kode_akun');
            $table->foreign('kode_akun')->references('id')->on('akun')->cascadeOnDelete();
        });
    }
};

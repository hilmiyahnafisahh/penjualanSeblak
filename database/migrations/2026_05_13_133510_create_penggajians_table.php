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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->string('id_penggajian')->unique();
            $table->string('id_karyawan')->constrained('karyawan')->onDelete('cascade');
            $table->string('periode');
            $table->date('tanggal_penggajian');
            $table->decimal('upah_per_jam', 15, 2); 
            $table->integer('jam_kerja');
            $table->integer('kehadiran')->default(24);
            $table->decimal('gaji_per_hari', 15, 2);
            $table->decimal('nominal', 15, 2);
            $table->enum('status', [
                'Dibayarkan',
                'Ditangguhkan',
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
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

            // periode gaji
            $table->string('periode');

            // tanggal penggajian
            $table->date('tanggal_penggajian');

            // upah tiap jam
            $table->decimal('upah_per_jam', 15, 2);

            // jam kerja per hari
            $table->integer('jam_kerja');

            // jumlah hadir dalam periode
            $table->integer('kehadiran')->default(24);

            // upah_per_jam × jam_kerja
            $table->decimal('gaji_per_hari', 15, 2);

            // gaji_per_hari × kehadiran
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
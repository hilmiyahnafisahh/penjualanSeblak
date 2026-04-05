<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id(); // primary key
            $table->string('id_layanan')->unique(); // kode layanan
            $table->string('nama_layanan');
            $table->string('deskripsi');
            $table->enum('status_layanan', ['Tersedia', 'Tidak Tersedia']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
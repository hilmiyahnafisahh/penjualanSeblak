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
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('id_menu', 10)->unique();
            $table->string('nama_menu', 50);
            $table->enum('kategori_menu', ['Makanan', 'Minuman']);
            $table->decimal('harga', 10, 2);
            $table->string('deskripsi', 255);
            $table->string('gambar_menu', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};

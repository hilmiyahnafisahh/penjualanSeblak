<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pembelian')->constrained('pembelian')->onDelete('cascade');
            $table->string('id_barang', 50)->nullable();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->onDelete('cascade');
            $table->decimal('harga_beli', 15, 2);
            $table->integer('jumlah');
            $table->date('tgl');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_barang');
    }
};
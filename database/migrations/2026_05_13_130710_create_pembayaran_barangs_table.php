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
        Schema::create('pembayaran_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pembelian')->constrained('pembelian')->onDelete('cascade');
            $table->date('tgl_bayar');
            $table->string('jenis_pembayaran');
            $table->string('nama_vendor');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_barang');
    }
};

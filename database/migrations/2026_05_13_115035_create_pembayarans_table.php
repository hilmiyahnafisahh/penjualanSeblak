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
        Schema::create('pembayaran', function (Blueprint $table) {

    $table->id();

    $table->foreignId('id_pemesanan')
        ->constrained('pemesanan')
        ->cascadeOnDelete();

    $table->string('id_pembayaran')->unique();
    $table->string('metode_pembayaran')->nullable();
    $table->dateTime('tanggal_pembayaran')->nullable();
    $table->decimal('total_pembayaran', 10, 2)->default(0);
    $table->string('status_pembayaran')->default('lunas');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
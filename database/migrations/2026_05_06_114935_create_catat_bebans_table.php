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
        Schema::create('catat_beban', function (Blueprint $table) {
        $table->id('id_beban');
        $table->string('kode_akun');
        $table->date('tanggal');
        $table->decimal('total', 12, 2);
        $table->text('keterangan')->nullable();
        $table->string('jenis_beban');
        $table->string('status')->default('lunas');
        $table->timestamps();

        $table->foreign('kode_akun')
          ->references('kode_akun')
          ->on('akun')
          ->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catat_beban');
    }
};

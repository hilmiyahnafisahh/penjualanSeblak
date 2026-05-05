<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//tambahan
use Illuminate\Support\Facades\DB;

class PembelianBarang extends Model
{
    use HasFactory;

    protected $table = 'pembelian_barang';
    
    // Sesuaikan fillable dengan kolom yang ada di migration pembelian_barang kamu
    protected $guarded = [];

   public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    } //belongsTo karena pembelian_barang hanya milik satu pembelian

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    } //belongsTo karena pembelian_barang hanya merujuk satu barang

}
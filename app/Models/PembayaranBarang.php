<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PembayaranBarang extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_barang';

    protected $guarded = [];

    // Relasi ke Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    // Scope untuk pembayaran yang masih memiliki sisa tagihan
    public function scopeMemilikiSisa($query)
    {
        return $query->where('sisa_tagihan', '>', 0);
    }

    // Scope untuk pembayaran bertipe tertentu
    public function scopeByJenisPembayaran($query, $jenis)
    {
        return $query->where('jenis_pembayaran', $jenis);
    }
}

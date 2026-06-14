<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// untuk tambahan db
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getKodeFakturBeli()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(id_pembelian), 'BL-0000000') as id_pembelian 
                FROM pembelian 
                WHERE id_pembelian LIKE 'BL-%'";
        $kodefaktur = DB::select($sql);

        foreach ($kodefaktur as $kdpmbl) {
            $kd = $kdpmbl->id_pembelian;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-7);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'BL-'.str_pad($noakhir,7,"0",STR_PAD_LEFT); //menyambung dengan string BL-00001
        return $noakhir;

    }

    protected static function boot()
{
    parent::boot();

    static::deleting(function ($pembelian) {
        foreach ($pembelian->barang as $item) {
            $barang = Barang::where('id_barang', $item->id_barang)->first();
            if ($barang) {
                $barang->decrement('stok', $item->jumlah);
            }
        }
    });
}


    // relasi ke tabel penjualan barang
    public function barang()
    {
        return $this->hasMany(PembelianBarang::class, 'id_pembelian'); //hasMany karena satu pembelian bisa memiliki banyak pembelian_barang
    }

    // relasi ke tabel pembayaran
    public function pembayaran()
    {
        return $this->hasMany(PembayaranBarang::class, 'id_pembelian');
    }

    // relasi ke tabel karyawan
    // ✅ BENAR
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
}

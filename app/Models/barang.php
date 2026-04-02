<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// tambahan
use Illuminate\Support\Facades\DB;



class barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $fillable = ['id_barang', 'nama_barang', 'stok', 'satuan', 'harga_beli', 'harga_jual'];

    public static function getKodeBarang()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(id_barang), 'BRG000') as kode_barang 
                FROM barang ";
        $kodebarang = DB::select($sql);

        // cacah hasilnya
        foreach ($kodebarang as $kdbrg) {
            $kd = $kdbrg->kode_barang;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'BRG'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;
    }
    public function setHargaBarangAttribute($value)
    {
        // Hapus koma (,) dari nilai sebelum menyimpannya ke database
        $this->attributes['harga_barang'] = str_replace('.', '', $value);
    }
}

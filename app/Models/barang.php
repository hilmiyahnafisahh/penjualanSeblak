<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use illuminate\support\Facades\DB;

class barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $fillable = ['id_barang', 'nama_barang', 'stok', 'satuan', 'harga_beli', 'harga_jual'];

    public static function getkodebarang()
    {
        // query untuk mendapatkan kode kategori terakhir
        $sql = "SELECT IFNULL(MAX(kode_barang), 'BRG000') AS kode_barang FROM barang";
        $kd_barang = DB::select($sql); //menjalankan query dan menyimpan hasilnya dalam variabel $result

        //cacah hasil query untuk mendapatkan kode kategori terakhir
        foreach ($kd_barang as $kdbrg) {
            $kd = $kdbrg->kode_barang; //mengambil nilai kode_kategori dari hasil query
        }

        //proses untuk membuat kode kategori baru dengan format "KAT" diikuti dengan angka yang diambil dari kode kategori terakhir
        $kdawal= substr($kd,-3); //mengambil 3 karakter terakhir dari kode kategori terakhir
        $kdawal++; //menambahkan 1 pada angka yang diambil dari kode kategori terakhir
        //atau $kdakhir=$kdawal+1
        $kdakhir= 'BRG'.str_pad($kdawal,3,"0",STR_PAD_LEFT); //menggabungkan "KAT" dengan angka yang sudah ditambahkan 1, dan memastikan bahwa angka tersebut memiliki 3 digit dengan menambahkan nol di depan jika diperlukan
        return $kdakhir; //mengembalikan kode kategori baru yang sudah dibuat
    }
}

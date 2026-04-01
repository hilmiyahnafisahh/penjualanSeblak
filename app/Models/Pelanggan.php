<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{       
    use HasFactory;
    protected $table = 'pelanggan'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getIDPelanggan()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(id_pelanggan), 'PLG000') as id_pelanggan
                FROM pelanggan";  
        $idpelanggan = DB::select($sql);

        // cacah hasilnya
        foreach ($idpelanggan as $IDPLG) {
            $ID = $IDPLG->id_pelanggan;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($ID,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'PLG'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;

    } 


}

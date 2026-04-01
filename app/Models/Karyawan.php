<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';  

    protected $guarded = [];
    
    public static function getKaryawanById($id_karyawan)
    {  
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(id_karyawan), 'KRY000') as id_karyawan 
                FROM karyawan ";
        $id_karyawan = DB::select($sql);

        // cacah hasilnya
        foreach ($id_karyawan as $idkry) {
            $id = $idkry->id_karyawan;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($id,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'KRY'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;

    }
}

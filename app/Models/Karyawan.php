<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';
    protected $guarded = [];

    public static function getIDKaryawan()
    {
        // ambil kode terakhir
        $sql = "SELECT IFNULL(MAX(id_karyawan), 'KRY000') as id_karyawan FROM karyawan";
        $idKaryawan = DB::select($sql);

        foreach ($idKaryawan as $idkry) {
            $id = $idkry->id_karyawan;
        }

        // generate kode baru
        $nomawal = substr($id, -3);
        $nomawal++;

        return 'KRY' . str_pad($nomawal, 3, "0", STR_PAD_LEFT);
    }
}
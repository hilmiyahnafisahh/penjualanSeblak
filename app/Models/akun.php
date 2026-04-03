<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class akun extends Model
{
    use HasFactory;

    protected $table = 'akun';
    protected $primaryKey = 'id';
    protected $fillable = ['kode_akun', 'nama_akun', 'jenis_akun'];

    public static function getkodeakun()
    {
        // query untuk mendapatkan kode akun terakhir
        $sql = "SELECT IFNULL(MAX(kode_akun), 'REF000') AS kode_akun FROM akun";
        $kd_akun = DB::select($sql);

        // ambil hasil
        foreach ($kd_akun as $kdkn) {
            $kd = $kdkn->kode_akun;
        }

        // generate kode baru
        $kdawal = substr($kd, -3);
        $kdawal++;

        $kdakhir = 'REF' . str_pad($kdawal, 3, "0", STR_PAD_LEFT);

        return $kdakhir;
    }
}
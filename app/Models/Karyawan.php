<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use App\Models\Penggajian;
use App\Models\Pembayaran;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    public $incrementing = false;
    protected $keyType = 'string';
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

    public function penggajian()
    {
        return $this->hasMany(Penggajian::class, 'id_karyawan');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_karyawan');
    }
}
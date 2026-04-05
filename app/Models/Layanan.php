<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; 

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $guarded = [];

    public static function getIDLayanan()
    {
        // ambil kode terakhir
        $sql = "SELECT IFNULL(MAX(id_layanan), 'LYN000') as id_layanan FROM layanan";
        $idLayanan = DB::select($sql);

        foreach ($idLayanan as $idly) {
            $id = $idly->id_layanan;
        }

        // generate kode baru
        $nomawal = substr($id, -3);
        $nomawal++;

        return 'LYN' . str_pad($nomawal, 3, "0", STR_PAD_LEFT);
    }
}
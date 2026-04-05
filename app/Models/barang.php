<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_barang',
        'nama_barang',
        'stok',
        'satuan',
        'harga_beli',
        'harga_jual'
    ];

    public static function getKodeBarang()
    {
        // ambil kode terakhir
        $sql = "SELECT IFNULL(MAX(id_barang), 'BRG000') as kode_barang FROM barang";
        $kodebarang = DB::select($sql);

        foreach ($kodebarang as $kdbrg) {
            $kd = $kdbrg->kode_barang;
        }

        // generate kode baru
        $noawal = substr($kd, -3);
        $noawal++;

        return 'BRG' . str_pad($noawal, 3, "0", STR_PAD_LEFT);
    }

    public function setHargaBarangAttribute($value)
    {
        // hapus titik dari format angka
        $this->attributes['harga_barang'] = str_replace('.', '', $value);
    }
}
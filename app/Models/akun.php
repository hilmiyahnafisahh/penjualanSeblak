<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Akun extends Model
{
    use HasFactory;

    protected $table = 'akun';

    protected $guarded = [];

    // Generate kode akun otomatis
    public static function getKodeAkun($prefix)
    {
        // Ambil kode terbesar berdasarkan prefix
        $last = self::where('kode_akun', 'like', $prefix . '%')
            ->max('kode_akun');

        if ($last) {

            // contoh: 1001 -> ambil angka lalu tambah 1
            $newKode = (int) $last + 1;

        } else {

            // jika belum ada
            $newKode = $prefix . '001';
        }

        return $newKode;
    }

    // Hilangkan titik saat disimpan
    public function setKodeAkunAttribute($value)
    {
        $this->attributes['kode_akun'] = str_replace('.', '', $value);
    }
    public function beban()
    {
        return $this->hasMany(CatatBeban::class, 'kode_akun', 'kode_akun');
    }
    // relasi ke tabel jurnal detail
    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'akun_id');
    }

}
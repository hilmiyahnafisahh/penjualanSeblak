<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class penggajian extends Model
{
    protected $table = 'penggajian';

    protected $fillable = [
        'id_penggajian',
        'id_karyawan',
        'status',
        'tanggal_penggajian',
        'upah_per_jam',
        'jam_kerja',
        'kehadiran',
        'gaji_per_hari',
        'nominal',
        'periode',
    ];

    public static function getIDPenggajian()
    {
        $sql = "SELECT IFNULL(MAX(id_penggajian), 'PGJ000') as id_penggajian FROM penggajian WHERE id_penggajian LIKE 'PGJ%'";
        $idPenggajian = DB::select($sql);

        foreach ($idPenggajian as $idpgj) {
            $id = $idpgj->id_penggajian;
        }

        $nomawal = substr($id, -3);
        $nomawal++;

        return 'PGJ' . str_pad($nomawal, 3, "0", STR_PAD_LEFT);
    }

    /**
     * Cek apakah karyawan sudah digaji pada periode tertentu.
     */
    public static function sudahDigaji(string $idKaryawan, string $periode, ?int $excludeId = null): bool
    {
        $query = static::where('id_karyawan', $idKaryawan)
            ->where('periode', $periode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
}

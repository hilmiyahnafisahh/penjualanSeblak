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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id_layanan = self::generateKodeLayanan();
        });
    }

    public static function generateKodeLayanan()
    {
        $last = DB::table('layanan')
            ->select('id_layanan')
            ->orderByDesc('id')
            ->first();

        if (!$last) {
            return 'LY001';
        }

        $lastKode = $last->id_layanan;

        $number = (int) substr($lastKode, -3);
        $number++;

        return 'LY' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
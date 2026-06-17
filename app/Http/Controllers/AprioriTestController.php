<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Phpml\Association\Apriori;

// tambahan untuk akses ke db
use Illuminate\Support\Facades\DB; //untuk menggunakan db

class AprioriTestController extends Controller
{
    public function test()
    {
        // 🔥 CONTOH DATA TRANSAKSI (manual dulu)
        $samples = [
            ['Bread', 'Milk'],
            ['Bread', 'Diaper', 'Beer', 'Eggs'],
            ['Milk', 'Diaper', 'Beer', 'Coke'],
            ['Bread', 'Milk', 'Diaper', 'Beer'],
            ['Bread', 'Milk', 'Diaper', 'Coke'],
        ];

        $labels = [];

        // 🔥 set parameter
        $minSupport = 0.4;     // minimal muncul 40%
        $minConfidence = 0.6;  // kepercayaan rule

        $associator = new Apriori($minSupport, $minConfidence);
        $associator->train($samples, $labels);

        $rules = $associator->getRules();

        dd($rules);
    }

    public function tes2(){
        // 🔥 Ambil semua transaksi + barang
        $data = DB::table('penjualan')
            ->join('penjualan_barang', 'penjualan.id', '=', 'penjualan_barang.penjualan_id')
            ->join('barang', 'penjualan_barang.barang_id', '=', 'barang.id')
            ->select('penjualan.id as transaksi_id', 'barang.nama_barang')
            ->orderBy('penjualan.id')
            ->get();

        // 🔥 Kelompokkan per transaksi
        $transaksi = [];

        foreach ($data as $row) {
            $transaksi[$row->transaksi_id][] = $row->nama_barang;
        }

        // 🔥 Reindex jadi array biasa
        $samples = array_values($transaksi);
        dd($samples); // cek dulu formatnya
        // ✅ TAMBAHKAN DI SINI untuk memfilter minimal 2 barang
        $samples = array_filter($samples, function ($items) {
            return count($items) > 1;
        });

        // (opsional tapi bagus) reset index array
        $samples = array_values($samples);

        dd($samples); // cek dulu formatnya

        // 🔥 Jalankan Apriori
        $labels = [];
        $associator = new Apriori(0.1, 0.1); // bisa kamu kecilkan dulu
        $associator->train($samples, $labels);

        $rules = $associator->getRules();

        dd($rules);
    }
}
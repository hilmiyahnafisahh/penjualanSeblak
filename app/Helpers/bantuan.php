<?php
 
// fungsi untuk mengembalikan format rupiah dari suatu nominal tertentu
// dengan pemisah ribuan 
function rupiah($nominal) {
    return "Rp ".number_format($nominal);
}

function dolar($nominal) {
    return "USD ".number_format($nominal);
}
// HITUNG GAJI PER HARI
function hitungGajiPerHari($jamKerja, $upahPerJam)
{
    return $jamKerja * $upahPerJam;
}

function hargajual ($harga_beli, $stok) {
    $harga_jual = ($harga_beli/$stok) * 1.4; // markup 40%
    return (int)round($harga_jual, 0); // pembulatan ke angka terdekat
}

// HITUNG TOTAL GAJI
function hitungTotalGaji($jamKerja, $upahPerJam, $hariKerja)
{
    return ($jamKerja * $upahPerJam) * $hariKerja;
}


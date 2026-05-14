<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;

// halaman awal
Route::get('/', function () {
    return view('welcome');
});

// untuk membuka halaman pembayaran
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// contoh sampel sederhana untuk mengetes midtrans
Route::get('/cekmidtrans', [App\Http\Controllers\CobaMidtransController::class, 'cekmidtrans']);

// contoh menggunakan callback
use App\Http\Controllers\CobaMidtransController;
// Route untuk menampilkan halaman tombol bayar & simulasi
Route::get('/cek-midtrans', [CobaMidtransController::class, 'cekmidtranscallback']);


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\BebanPdfController;
use App\Http\Controllers\CobaMidtransController;

// halaman awal
Route::get('/', function () {
    return view('welcome');
});

// untuk membuka halaman pembayaran pemesanan
Route::get('/pembayaran/{id}', [App\Http\Controllers\PembayaranController::class, 'show'])->name('pembayaran.show');
Route::post('/pembayaran/{id}', [App\Http\Controllers\PembayaranController::class, 'store'])->name('pembayaran.store');

// untuk membuka halaman pembayaran beban
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// untuk membuka halaman pembayaran pemesanan lewat Midtrans
Route::get('/bayar-pemesanan/{id}', [MidtransController::class, 'bayarPemesanan'])
    ->name('pembayaran.midtrans');

// contoh sampel sederhana untuk mengetes midtrans
Route::get('/cekmidtrans', [CobaMidtransController::class, 'cekmidtrans']);

// Route untuk menampilkan halaman tombol bayar & simulasi
Route::get('/cek-midtrans', [CobaMidtransController::class, 'cekmidtranscallback']);

Route::get('/payment/{id}', [PaymentController::class, 'index'])->name('payment.page');
Route::post('/payment/{id}', [PaymentController::class, 'pay']);

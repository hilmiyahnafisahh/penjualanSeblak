<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\BebanPdfController;
use App\Http\Controllers\CobaMidtransController;
use App\Http\Controllers\PembayaranPdfController;
use App\Http\Controllers\PembelianPdfController;
use App\Http\Controllers\Admin\AuthController;

// Halaman awal
Route::get('/', function () {
    return view('welcome');
});

// Pembayaran Beban
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// Pembayaran Pemesanan
Route::get('/bayar-pemesanan/{id}', [MidtransController::class, 'bayarPemesanan'])
    ->name('pembayaran.midtrans');

// Pembayaran Penggajian
Route::get('/bayar-penggajian/{id}', [MidtransController::class, 'bayarPenggajian'])
    ->name('penggajian.midtrans');

// Callback Penggajian
Route::post('/midtrans/penggajian/success/{id}', [MidtransController::class, 'successPenggajian'])
    ->name('penggajian.midtrans.success');

// Test Midtrans
Route::get('/cekmidtrans', [CobaMidtransController::class, 'cekmidtrans']);
Route::get('/cek-midtrans', [CobaMidtransController::class, 'cekmidtranscallback']);

// PDF Pembayaran
Route::get('/admin/pembayaran/{id}/invoice', [PembayaranPdfController::class, 'download'])
    ->name('pembayaran.invoice');

// PDF Pembelian
Route::get('/pembelian/pdf', [PembelianPdfController::class, 'pembelian'])
    ->name('pembelian.pdf');

// ======================
// AUTH ADMIN
// ======================

Route::get('/admin/login', [AuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.post');

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout');

Route::match(['get', 'post'], '/admin/seblak-logout', [AuthController::class, 'logout'])
    ->name('filament.admin.auth.logout');

// Filament expects a named login route like `filament.admin.auth.login`.
// Provide alias routes that point to the existing admin auth controller
// so Filament's route() calls resolve correctly.
Route::get('/admin/seblak-login', [AuthController::class, 'showLogin'])
    ->name('filament.admin.auth.login');

Route::post('/admin/seblak-login', [AuthController::class, 'login'])
    ->name('filament.admin.auth.login.post');

// POS routes removed — using Filament admin panel as main admin
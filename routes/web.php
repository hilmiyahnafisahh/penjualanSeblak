<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\BebanPdfController;
use App\Http\Controllers\CobaMidtransController;
use App\Http\Controllers\PembayaranPdfController;
use App\Http\Controllers\PembelianPdfController;
use App\Http\Controllers\PengirimanEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;

// Halaman awal
Route::get('/', function () {
    return view('welcome');
});

// Customer frontend
Route::get('/depan', [CustomerController::class, 'index'])->name('depan');

Route::middleware('auth')->group(function () {
    Route::get('/lihatkeranjang', [CustomerController::class, 'cart'])->name('cart.index');
    Route::post('/tambah', [CustomerController::class, 'addToCart'])->name('cart.add');
    Route::delete('/hapus/{barang_id}', [CustomerController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/lihatriwayat', [CustomerController::class, 'history'])->name('history');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


// Untuk membuka halaman pembayaran beban
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// Untuk membuka halaman pembayaran pemesanan lewat Midtrans
Route::get('/bayar-pemesanan/{id}', [MidtransController::class, 'bayarPemesanan'])
    ->name('pembayaran.midtrans');

// Untuk membuka halaman pembayaran penggajian lewat Midtrans
Route::get('/bayar-penggajian/{id}', [MidtransController::class, 'bayarPenggajian'])
    ->name('penggajian.midtrans');

// Untuk menerima hasil sukses pembayaran gaji dari front-end
Route::post('/midtrans/penggajian/success/{id}', [MidtransController::class, 'successPenggajian'])
    ->name('penggajian.midtrans.success');

// Contoh sampel sederhana untuk mengetes midtrans
Route::get('/cekmidtrans', [CobaMidtransController::class, 'cekmidtrans']);

// Route untuk menampilkan halaman tombol bayar & simulasi
Route::get('/cek-midtrans', [CobaMidtransController::class, 'cekmidtranscallback']);

// Unduh invoice PDF pembayaran
Route::get('/admin/pembayaran/{id}/invoice', [PembayaranPdfController::class, 'download'])
    ->name('pembayaran.invoice');

// Untuk mendownload PDF pembelian
Route::get('/pembelian/pdf', [PembelianPdfController::class, 'pembelian'])
    ->name('pembelian.pdf');

// ============================================================
// Pengiriman Email
// ============================================================

// ✅ Route autorefresh — dipanggil dari browser sesuai modul
Route::get('/proses_pengiriman_email_pemesanan', [PengirimanEmailController::class, 'kirimSemua'])
    ->name('pengiriman-email.proses');

// Daftar riwayat pengiriman email
Route::get('/pengiriman-email', [PengirimanEmailController::class, 'index'])
    ->name('pengiriman-email.index');

// Kirim invoice per pesanan dari tombol
Route::get('/pengiriman-email/kirim/{id}', [PengirimanEmailController::class, 'kirim'])
    ->name('pengiriman-email.kirim');

// Hapus riwayat
Route::delete('/pengiriman-email/{id}', [PengirimanEmailController::class, 'destroy'])
    ->name('pengiriman-email.destroy');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\BebanPdfController;
    

// halaman awal
Route::get('/', function () {
    return view('welcome');
});

// untuk membuka halaman pembayaran
Route::get('/bayar-beban/{id}', [MidtransController::class, 'bayar'])
    ->name('beban.bayar');

// untuk mendownload invoice pdf
Route::get('/download-invoice/{id}', [BebanPdfController::class, 'download'])
    ->name('beban.download');   




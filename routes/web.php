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




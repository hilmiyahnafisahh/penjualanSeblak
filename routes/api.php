<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// routes/api.php untuk mengolah keranjang
Route::post('/midtrans-callback', [App\Http\Controllers\KeranjangController::class, 'handleCallback']);

// ========================================
// CALLBACK / WEBHOOK MIDTRANS
// ========================================

// CALLBACK MIDTRANS
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);
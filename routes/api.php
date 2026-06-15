<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\CobaMidtransController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// routes/api.php untuk mengolah keranjang
Route::post('/midtrans-callback', [CobaMidtransController::class, 'handleCallback']);

// ========================================
// CALLBACK / WEBHOOK MIDTRANS
// ========================================

// CALLBACK MIDTRANS
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);

// CHECK STATUS PEMBAYARAN
Route::get('/pembayaran/{id}/status', [MidtransController::class, 'checkStatus']);
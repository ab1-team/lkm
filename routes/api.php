<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('holding.license')->prefix('v1/holding/laporan')->group(function () {
    Route::get('neraca', [\App\Http\Controllers\Api\HoldingLaporanController::class, 'neraca']);
    Route::get('laba-rugi', [\App\Http\Controllers\Api\HoldingLaporanController::class, 'labaRugi']);
    Route::get('arus-kas', [\App\Http\Controllers\Api\HoldingLaporanController::class, 'arusKas']);
    Route::get('perubahan-ekuitas', [\App\Http\Controllers\Api\HoldingLaporanController::class, 'perubahanEkuitas']);
    Route::get('calk', [\App\Http\Controllers\Api\HoldingLaporanController::class, 'calk']);
});

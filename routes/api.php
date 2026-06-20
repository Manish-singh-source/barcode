<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\BarcodeController as ApiBarcodeController;
use App\Http\Controllers\Api\V1\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\V1\ProductController as ApiProductController;
use App\Http\Controllers\Api\V1\ScanController as ApiScanController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/me', [ApiAuthController::class, 'me']);
    });
});

Route::post('/scan', [ApiScanController::class, 'scan']);
Route::middleware('auth:sanctum')->get('/scan/history', [ApiScanController::class, 'history']);
Route::get('/scan/{unique_code}', [ApiScanController::class, 'scanByGet']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/products', [ApiProductController::class, 'index']);
    Route::get('/barcodes', [ApiBarcodeController::class, 'index']);
    Route::get('/barcodes/{id}', [ApiBarcodeController::class, 'show'])->whereNumber('id');
    Route::put('/barcodes/{id}', [ApiBarcodeController::class, 'update'])->whereNumber('id');
    Route::delete('/barcodes/{id}', [ApiBarcodeController::class, 'destroy'])->whereNumber('id');
    Route::get('/barcodes/check-duplicate', [ApiBarcodeController::class, 'checkDuplicate']);
    Route::post('/barcodes/generate', [ApiBarcodeController::class, 'generate']);

    Route::prefix('dashboard')->group(function (): void {
        Route::get('/stats', [ApiDashboardController::class, 'stats']);
        Route::get('/recent-barcodes', [ApiDashboardController::class, 'recentBarcodes']);
    });
});
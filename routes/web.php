<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BarcodeWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ScannerController;
use Illuminate\Support\Facades\Route;

$shortHost = parse_url(config('barcode.short_url_base', 'https://bc1.in'), PHP_URL_HOST);

if ($shortHost) {
    Route::domain($shortHost)->group(function (): void {
        Route::get('/{unique_code}', [BarcodeWebController::class, 'publicShow'])
            ->where('unique_code', '[A-Za-z0-9]+')
            ->name('barcodes.short-public-show');
    });
}

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
Route::get('/barcodes/generate', [BarcodeController::class, 'generateForm'])->name('barcodes.generate');
Route::get('/barcodes', [BarcodeWebController::class, 'index'])->name('barcodes.index');
Route::put('/barcodes/{id}', [BarcodeWebController::class, 'update'])->whereNumber('id')->name('barcodes.update');
Route::delete('/barcodes/{id}', [BarcodeWebController::class, 'destroy'])->whereNumber('id')->name('barcodes.destroy');
Route::get('/barcodes/{id}', [BarcodeWebController::class, 'show'])->whereNumber('id')->name('barcodes.show');
Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
Route::get('/b/{unique_code}', [BarcodeWebController::class, 'publicShow'])->name('barcodes.public-show');

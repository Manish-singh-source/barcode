<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BarcodeWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
Route::get('/barcodes/generate', [BarcodeController::class, 'generateForm'])->name('barcodes.generate');
Route::get('/barcodes', [BarcodeWebController::class, 'index'])->name('barcodes.index');
Route::get('/barcodes/{id}', [BarcodeWebController::class, 'show'])->name('barcodes.show');
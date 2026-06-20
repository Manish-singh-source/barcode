<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');

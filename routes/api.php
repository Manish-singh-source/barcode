<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\DashboardController as ApiDashboardController;
use App\Models\BarcodeGeneration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/scan/{unique_code}', function (string $unique_code): JsonResponse {
    $barcode = BarcodeGeneration::query()
        ->with('product')
        ->where('unique_code', $unique_code)
        ->first();

    if (! $barcode || ! $barcode->product) {
        return response()->json([
            'message' => 'Invalid barcode. No product found for this code.',
        ], 404);
    }

    $product = $barcode->product;

    return response()->json([
        'message' => 'Product found.',
        'data' => [
            'unique_code' => $barcode->unique_code,
            'product_name' => $product->name,
            'description' => $product->description,
            'sku' => $product->sku,
            'price' => $product->price,
            'brand' => $product->brand,
            'category' => $product->category,
        ],
    ]);
});

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

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function (): void {
    Route::get('/stats', [ApiDashboardController::class, 'stats']);
    Route::get('/recent-barcodes', [ApiDashboardController::class, 'recentBarcodes']);
});

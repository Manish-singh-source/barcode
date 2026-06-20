<?php

use App\Models\BarcodeGeneration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

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


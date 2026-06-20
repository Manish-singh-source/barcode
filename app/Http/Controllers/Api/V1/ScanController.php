<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function show(string $unique_code): JsonResponse
    {
        return $this->lookupByCode($unique_code);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unique_code' => ['required', 'string', 'max:255'],
        ]);

        return $this->lookupByCode($validated['unique_code']);
    }

    private function lookupByCode(string $unique_code): JsonResponse
    {
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
    }
}
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);

        $products = Product::query()
            ->orderBy('name')
            ->paginate($perPage);

        return $this->successResponse([
            'data' => $products->getCollection()->map(static function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 'Products loaded successfully.');
    }
}
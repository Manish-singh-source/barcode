<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Models\Product;
use App\Models\ScanLog;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function stats(Request $request): JsonResponse
    {
        if (($request->user()?->role?->value ?? $request->user()?->role) !== 'admin') {
            return $this->errorResponse('Forbidden', 403);
        }

        return $this->successResponse([
            'total_barcodes' => BarcodeGeneration::query()->count(),
            'scans_today' => ScanLog::query()->whereDate('created_at', today())->count(),
            'total_products' => Product::query()->count(),
            'active_users' => User::query()->count(),
        ], 'Dashboard stats loaded.');
    }

    public function recentBarcodes(Request $request): JsonResponse
    {
        if (($request->user()?->role?->value ?? $request->user()?->role) !== 'admin') {
            return $this->errorResponse('Forbidden', 403);
        }

        $recent = BarcodeGeneration::query()
            ->with(['user', 'product'])
            ->latest()
            ->paginate(10);

        return $this->successResponse([
            'data' => $recent->getCollection()->map(function (BarcodeGeneration $barcode): array {
                return [
                    'id' => $barcode->id,
                    'unique_code' => $barcode->unique_code,
                    'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
                    'custom_label' => $barcode->custom_label,
                    'created_at' => $barcode->created_at?->toDateTimeString(),
                    'user' => $barcode->user ? [
                        'id' => $barcode->user->id,
                        'name' => $barcode->user->name,
                    ] : null,
                    'product' => $barcode->product ? [
                        'id' => $barcode->product->id,
                        'name' => $barcode->product->name,
                    ] : null,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $recent->currentPage(),
                'last_page' => $recent->lastPage(),
                'per_page' => $recent->perPage(),
                'total' => $recent->total(),
            ],
        ], 'Recent barcodes loaded.');
    }
}

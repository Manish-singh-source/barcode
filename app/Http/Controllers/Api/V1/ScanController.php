<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ScanResult;
use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Models\ScanLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    use ApiResponseTrait;

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unique_code' => ['required', 'string', 'max:100'],
        ]);

        return $this->scanByCode($validated['unique_code'], $request);
    }

    public function scanByGet(Request $request, string $unique_code): JsonResponse
    {
        return $this->scanByCode($unique_code, $request);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);

        $logs = ScanLog::query()
            ->with(['barcodeGeneration.product'])
            ->where('scanned_by', Auth::id())
            ->latest('created_at')
            ->paginate($perPage);

        return $this->successResponse([
            'data' => $logs->getCollection()->map(static function (ScanLog $log): array {
                $product = $log->barcodeGeneration?->product;

                return [
                    'unique_code' => $log->unique_code,
                    'scan_result' => $log->scan_result instanceof ScanResult ? $log->scan_result->value : (string) $log->scan_result,
                    'created_at' => $log->created_at?->toISOString(),
                    'product_data_snapshot' => $log->product_data_snapshot,
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'description' => $product->description,
                        'price' => $product->price,
                        'brand' => $product->brand,
                        'category' => $product->category,
                        'unit' => $product->unit,
                        'stock_quantity' => $product->stock_quantity,
                    ] : null,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ], 'Scan history loaded successfully.');
    }

    private function scanByCode(string $uniqueCode, Request $request): JsonResponse
    {
        $barcode = BarcodeGeneration::query()
            ->with('product')
            ->where('unique_code', $uniqueCode)
            ->first();

        if (! $barcode) {
            $this->logScan($request, [
                'barcode_generation_id' => null,
                'unique_code' => $uniqueCode,
                'scan_result' => ScanResult::Invalid,
                'product_data_snapshot' => null,
            ]);

            return $this->errorResponse('Invalid barcode. No product found.', 404);
        }

        $product = $barcode->product;

        $snapshot = [
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $barcode->barcode_format instanceof \BackedEnum ? $barcode->barcode_format->value : (string) $barcode->barcode_format,
            'custom_label' => $barcode->custom_label,
            'product' => $product ? [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'brand' => $product->brand,
                'category' => $product->category,
                'description' => $product->description,
                'unit' => $product->unit,
                'stock_quantity' => $product->stock_quantity,
            ] : null,
        ];

        $this->logScan($request, [
            'barcode_generation_id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
            'scan_result' => ScanResult::Success,
            'product_data_snapshot' => $snapshot,
        ]);

        return $this->successResponse([
            'valid' => true,
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $snapshot['barcode_format'],
            'custom_label' => $barcode->custom_label,
            'barcode_image_url' => $barcode->barcode_image_url,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'price' => $product->price,
                'brand' => $product->brand,
                'category' => $product->category,
                'unit' => $product->unit,
                'stock_quantity' => $product->stock_quantity,
            ] : null,
            'scanned_at' => now()->toISOString(),
        ], 'Barcode scanned successfully.');
    }

    private function logScan(Request $request, array $data): void
    {
        ScanLog::create([
            'scanned_by' => Auth::id(),
            'barcode_generation_id' => $data['barcode_generation_id'],
            'unique_code' => $data['unique_code'],
            'raw_scan_data' => $request->input('unique_code', $data['unique_code']),
            'scan_result' => $data['scan_result']->value,
            'product_data_snapshot' => $data['product_data_snapshot'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
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
            ->where('scanned_by', Auth::id())
            ->latest('created_at')
            ->paginate($perPage);

        return $this->successResponse([
            'data' => $logs->getCollection()->map(static function (ScanLog $log): array {
                return [
                    'unique_code' => $log->unique_code,
                    'scan_result' => $log->scan_result instanceof ScanResult ? $log->scan_result->value : (string) $log->scan_result,
                    'created_at' => $log->created_at?->toISOString(),
                    'product_data_snapshot' => $log->product_data_snapshot,
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
        $normalizedCode = $this->normalizeScannedCode($uniqueCode);

        $barcode = BarcodeGeneration::query()
            ->where('unique_code', $normalizedCode)
            ->first();

        if (! $barcode) {
            $this->logScan($request, $uniqueCode, null, ScanResult::Invalid, null);

            return $this->errorResponse('Invalid barcode. No product found.', 404);
        }

        $snapshot = [
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'custom_label' => $barcode->custom_label,
            'product' => $barcode->resolvedProductSnapshot(),
        ];

        $this->logScan($request, $barcode->unique_code, $barcode->id, ScanResult::Success, $snapshot);

        return $this->successResponse([
            'valid' => true,
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $snapshot['barcode_format'],
            'custom_label' => $barcode->custom_label,
            'barcode_data' => $barcode->barcode_data,
            'public_url' => $barcode->public_url ?? rtrim((string) config('barcode.short_url_base', 'https://wpc.bar'), '/') . '/' . $barcode->unique_code,
            'barcode_image_url' => $barcode->barcode_image_url,
            'product_name' => $snapshot['product']['name'] ?? $barcode->barcode_data,
            'product' => $snapshot['product'],
            'scanned_at' => now()->toISOString(),
        ], 'Barcode scanned successfully.');
    }

    private function normalizeScannedCode(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if (preg_match('~^https?://[^/]+/([A-Za-z0-9]+)$~i', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('~^/([A-Za-z0-9]+)$~i', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    private function logScan(Request $request, string $uniqueCode, ?int $barcodeGenerationId, ScanResult $result, ?array $snapshot): void
    {
        ScanLog::create([
            'scanned_by' => Auth::id(),
            'barcode_generation_id' => $barcodeGenerationId,
            'unique_code' => $uniqueCode,
            'raw_scan_data' => $request->input('unique_code', $uniqueCode),
            'scan_result' => $result->value,
            'product_data_snapshot' => $snapshot,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}







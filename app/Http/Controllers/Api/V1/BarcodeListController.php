<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeListController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = max((int) $request->input('length', 10), 1);
        $search = trim((string) data_get($request->input('search', []), 'value', ''));
        $orderColumnIndex = (int) data_get($request->input('order', []), '0.column', 0);
        $orderDirection = strtolower((string) data_get($request->input('order', []), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $columnMap = [
            0 => 'id',
            1 => 'unique_code',
            2 => 'barcode_format',
            3 => 'custom_label',
            4 => 'product_id',
            5 => 'created_at',
        ];

        $query = BarcodeGeneration::query()->with('product');
        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('unique_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode_data', 'like', '%' . $search . '%')
                    ->orWhere('custom_label', 'like', '%' . $search . '%')
                    ->orWhereHas('product', function ($productQuery) use ($search): void {
                        $productQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();
        $orderColumn = $columnMap[$orderColumnIndex] ?? 'created_at';

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->values()->map(static function (BarcodeGeneration $barcode, int $index) use ($start): array {
            return [
                'id' => $barcode->id,
                'row_number' => $start + $index + 1,
                'unique_code' => $barcode->unique_code,
                'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
                'custom_label' => $barcode->custom_label,
                'product_name' => $barcode->product?->name,
                'created_at' => $barcode->created_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function update(Request $request, BarcodeGeneration $barcode): JsonResponse
    {
        $validated = $request->validate([
            'custom_label' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $barcode->update([
            'custom_label' => $validated['custom_label'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
        ]);

        return $this->successResponse([
            'id' => $barcode->id,
            'custom_label' => $barcode->custom_label,
            'product_id' => $barcode->product_id,
        ], 'Barcode updated successfully.');
    }

    public function destroy(BarcodeGeneration $barcode): JsonResponse
    {
        $barcode->delete();

        return $this->successResponse(null, 'Barcode deleted successfully.');
    }

    public function show(BarcodeGeneration $barcode): JsonResponse
    {
        $barcode->load('product');

        return $this->successResponse([
            'id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'barcode_data' => $barcode->barcode_data,
            'custom_label' => $barcode->custom_label,
            'product' => $barcode->product ? [
                'id' => $barcode->product->id,
                'name' => $barcode->product->name,
                'sku' => $barcode->product->sku,
            ] : null,
            'created_at' => $barcode->created_at?->toISOString(),
        ], 'Barcode loaded successfully.');
    }
}
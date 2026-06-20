<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BarcodeFormat;
use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeController extends Controller
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

        $query = BarcodeGeneration::query()->with(['user', 'product'])->whereNull('deleted_at');
        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('unique_code', 'like', '%' . $this->escapeLike($search) . '%')
                    ->orWhere('custom_label', 'like', '%' . $this->escapeLike($search) . '%')
                    ->orWhereHas('product', function ($productQuery) use ($search): void {
                        $productQuery->where('name', 'like', '%' . $this->escapeLike($search) . '%');
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
                'product_id' => $barcode->product_id,
                'product_name' => $barcode->product?->name,
                'user_name' => $barcode->user?->name,
                'barcode_image_url' => $barcode->barcode_image_path ? Storage::disk('public')->url($barcode->barcode_image_path) : null,
                'created_at' => $barcode->created_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => ['required', 'string', 'max:500'],
        ]);

        $data = $validated['data'];
        $count = BarcodeGeneration::query()
            ->where('barcode_data', 'like', '%' . $this->escapeLike($data) . '%')
            ->count();

        return $this->successResponse([
            'exists' => $count > 0,
            'count' => $count,
        ], 'Duplicate check completed.');
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_data' => ['required', 'string', 'max:500'],
            'barcode_format' => ['required', 'in:code128,qrcode,code39,ean13'],
            'custom_label' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $product = null;
        if (! empty($validated['product_id'])) {
            $product = Product::query()->find($validated['product_id']);
        }

        $uniqueCode = $this->generateUniqueCode();
        $format = $validated['barcode_format'];
        $barcodePayload = $this->resolveBarcodePayload($uniqueCode, $format, $validated['barcode_data']);

        [$pngBinary, $svgMarkup] = $this->makeBarcodeAssets($barcodePayload, $format);

        $barcodePath = 'barcodes/' . $uniqueCode . '.png';
        Storage::disk('public')->put($barcodePath, $pngBinary);

        $barcode = BarcodeGeneration::query()->create([
            'user_id' => $request->user()?->id,
            'product_id' => $product?->id,
            'unique_code' => $uniqueCode,
            'barcode_format' => $format,
            'barcode_data' => $validated['barcode_data'],
            'barcode_image_path' => $barcodePath,
            'custom_label' => $validated['custom_label'] ?? null,
            'is_active' => true,
        ]);

        return $this->successResponse([
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'barcode_image_base64' => base64_encode($pngBinary),
            'barcode_svg' => $svgMarkup,
            'barcode_image_url' => Storage::disk('public')->url($barcodePath),
            'custom_label' => $barcode->custom_label,
            'created_at' => $barcode->created_at?->toISOString(),
        ], 'Barcode generated successfully.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'custom_label' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $barcode = BarcodeGeneration::query()->whereKey($id)->firstOrFail();
        $barcode->fill([
            'custom_label' => $validated['custom_label'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
        ])->save();

        $barcode->load('product');

        return $this->successResponse([
            'id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'custom_label' => $barcode->custom_label,
            'product_id' => $barcode->product_id,
            'product_name' => $barcode->product?->name,
            'user_name' => $barcode->user?->name,
            'barcode_image_url' => $barcode->barcode_image_path ? Storage::disk('public')->url($barcode->barcode_image_path) : null,
            'created_at' => $barcode->created_at?->format('Y-m-d H:i'),
        ], 'Barcode updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $barcode = BarcodeGeneration::query()->whereKey($id)->firstOrFail();
        $barcode->delete();

        return $this->successResponse(null, 'Barcode deleted.');
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'BC' . strtoupper(uniqid());
        } while (BarcodeGeneration::query()->where('unique_code', $code)->exists());

        return $code;
    }

    private function resolveBarcodePayload(string $uniqueCode, string $format, string $barcodeData): string
    {
        if ($format === 'ean13') {
            $digits = preg_replace('/\D+/', '', $uniqueCode . $barcodeData) ?: '';
            $payload = substr($digits . '0000000000000', 0, 12);

            return $payload;
        }

        return $uniqueCode;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function makeBarcodeAssets(string $payload, string $format): array
    {
        $pngGenerator = new BarcodeGeneratorPNG();
        $svgGenerator = new BarcodeGeneratorSVG();
        $type = $this->mapFormatToPicqerType($format);

        $png = $pngGenerator->getBarcode($payload, $type, 3, 100);
        $svg = $svgGenerator->getBarcode($payload, $type, 3, 100);

        return [$png, $svg];
    }

    private function mapFormatToPicqerType(string $format): string
    {
        return match ($format) {
            'code39' => BarcodeGeneratorPNG::TYPE_CODE_39,
            'ean13' => BarcodeGeneratorPNG::TYPE_EAN_13,
            'qrcode' => BarcodeGeneratorPNG::TYPE_CODE_128,
            default => BarcodeGeneratorPNG::TYPE_CODE_128,
        };
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
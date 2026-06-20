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
use RuntimeException;

class BarcodeController extends Controller
{
    use ApiResponseTrait;

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
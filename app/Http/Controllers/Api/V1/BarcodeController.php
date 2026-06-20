<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BarcodeFormat;
use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeController extends Controller
{
    use ApiResponseTrait;

    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 100);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);

        return $this->successResponse([
            'data' => $products->getCollection()->map(static function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'category' => $product->category,
                    'brand' => $product->brand,
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

    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => ['required', 'string', 'max:2000'],
        ]);

        $needle = trim(preg_replace('/\s+/u', ' ', $validated['data']) ?? '');
        $normalized = mb_strtolower($needle);

        $duplicate = BarcodeGeneration::query()
            ->whereRaw('LOWER(barcode_data) = ?', [$normalized])
            ->exists();

        $similar = ! $duplicate && BarcodeGeneration::query()
            ->whereRaw('LOWER(barcode_data) LIKE ?', ['%' . $normalized . '%'])
            ->exists();

        return $this->successResponse([
            'duplicate' => $duplicate,
            'similar' => $similar,
        ], $duplicate
            ? 'Exact barcode data already exists.'
            : ($similar ? 'Similar barcode data found.' : 'Barcode data appears unique.')
        );
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_data' => ['required', 'string', 'max:2000'],
            'custom_label' => ['nullable', 'string', 'max:255'],
            'barcode_format' => ['required', 'in:' . implode(',', array_map(static fn (BarcodeFormat $format): string => $format->value, BarcodeFormat::cases()))],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $barcodeData = trim($validated['barcode_data']);
        $customLabel = trim((string) ($validated['custom_label'] ?? '')) ?: null;
        $format = BarcodeFormat::from($validated['barcode_format']);
        $productId = $validated['product_id'] ?? null;
        $product = $productId ? Product::query()->find($productId) : null;

        if ($format === BarcodeFormat::Ean13 && ! preg_match('/^\d{12,13}$/', $barcodeData)) {
            return $this->errorResponse('EAN-13 requires 12 or 13 digits.', 422);
        }

        $uniqueCode = $this->generateUniqueCode();

        try {
            [$pngDataUrl, $svgMarkup] = $this->makeBarcodeAssets($barcodeData, $format);
        } catch (\RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }

        $barcode = BarcodeGeneration::query()->create([
            'user_id' => $request->user()?->id,
            'product_id' => $product?->id,
            'unique_code' => $uniqueCode,
            'barcode_format' => $format,
            'barcode_data' => $barcodeData,
            'barcode_image_path' => null,
            'custom_label' => $customLabel,
            'is_active' => true,
        ]);

        return $this->successResponse([
            'barcode' => [
                'id' => $barcode->id,
                'unique_code' => $barcode->unique_code,
                'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
                'barcode_data' => $barcode->barcode_data,
                'custom_label' => $barcode->custom_label,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ] : null,
            ],
            'barcode_image' => $pngDataUrl,
            'svg' => $svgMarkup,
            'human_readable_text' => $customLabel ?: $barcodeData,
            'download' => [
                'png_filename' => $uniqueCode . '.png',
                'svg_filename' => $uniqueCode . '.svg',
            ],
        ], 'Barcode generated successfully.', 201);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (BarcodeGeneration::query()->where('unique_code', $code)->exists());

        return $code;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function makeBarcodeAssets(string $barcodeData, BarcodeFormat $format): array
    {
        if ($format === BarcodeFormat::Qrcode) {
            return $this->makeQrAssets($barcodeData);
        }

        $type = match ($format) {
            BarcodeFormat::Code39 => BarcodeGeneratorPNG::TYPE_CODE_39,
            BarcodeFormat::Ean13 => BarcodeGeneratorPNG::TYPE_EAN_13,
            default => BarcodeGeneratorPNG::TYPE_CODE_128,
        };

        $pngGenerator = new BarcodeGeneratorPNG();
        $svgGenerator = new BarcodeGeneratorSVG();

        $png = $pngGenerator->getBarcode($barcodeData, $type, 3, 100);
        $svg = $svgGenerator->getBarcode($barcodeData, $type, 3, 100);

        return [
            'data:image/png;base64,' . base64_encode($png),
            $svg,
        ];
    }

    /**
     * @return array{0:string,1:string}
     */
    private function makeQrAssets(string $barcodeData): array
    {
        $response = Http::timeout(15)->get('https://api.qrserver.com/v1/create-qr-code/', [
            'size' => '400x400',
            'data' => $barcodeData,
            'margin' => 2,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('QR code generation is temporarily unavailable.');
        }

        $pngDataUrl = 'data:image/png;base64,' . base64_encode($response->body());
        $escapedPng = htmlspecialchars($pngDataUrl, ENT_QUOTES, 'UTF-8');
        $svgMarkup = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400" role="img" aria-label="QR code">
    <rect width="100%" height="100%" fill="#ffffff" />
    <image href="{$escapedPng}" x="0" y="0" width="400" height="400" preserveAspectRatio="xMidYMid meet" />
</svg>
SVG;

        return [$pngDataUrl, $svgMarkup];
    }
}
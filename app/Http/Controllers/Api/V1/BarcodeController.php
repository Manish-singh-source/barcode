<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $length = max((int) $request->input('length', 5), 1);
        $search = trim((string) data_get($request->input('search', []), 'value', ''));
        $orderColumnIndex = (int) data_get($request->input('order', []), '0.column', 0);
        $orderDirection = strtolower((string) data_get($request->input('order', []), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $columnMap = [
            0 => 'id',
            1 => 'unique_code',
            2 => 'barcode_format',
            3 => 'custom_label',
            4 => 'barcode_data',
            5 => 'created_at',
        ];

        $query = BarcodeGeneration::query()->with('user')->whereNull('deleted_at');
        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $escaped = $this->escapeLike($search);
                $subQuery->where('unique_code', 'like', '%' . $escaped . '%')
                    ->orWhere('custom_label', 'like', '%' . $escaped . '%')
                    ->orWhere('barcode_data', 'like', '%' . $escaped . '%');
            });
        }

        $recordsFiltered = (clone $query)->count();
        $orderColumn = $columnMap[$orderColumnIndex] ?? 'created_at';

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->values()->map(static function (BarcodeGeneration $barcode, int $index) use ($start): array {
            $snapshot = $barcode->resolvedProductSnapshot();

            return [
                'id' => $barcode->id,
                'row_number' => $start + $index + 1,
                'unique_code' => $barcode->unique_code,
            'public_url' => $barcode->public_url ?? BarcodeGeneration::publicUrlForCode($barcode->unique_code),
                'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
                'custom_label' => $barcode->custom_label,
                'barcode_data' => $barcode->barcode_data,
                'product_name' => $snapshot['name'] ?? $barcode->barcode_data,
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

    public function show(int $id): JsonResponse
    {
        $barcode = BarcodeGeneration::query()
            ->with('user')
            ->withCount('scanLogs')
            ->withMax('scanLogs', 'created_at')
            ->findOrFail($id);

        $snapshot = $barcode->resolvedProductSnapshot();

        return $this->successResponse([
            'id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
            'public_url' => $barcode->public_url ?? BarcodeGeneration::publicUrlForCode($barcode->unique_code),
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'barcode_data' => $barcode->barcode_data,
            'custom_label' => $barcode->custom_label,
            'barcode_image_url' => $barcode->barcode_image_path ? Storage::disk('public')->url($barcode->barcode_image_path) : null,
            'barcode_image_path' => $barcode->barcode_image_path,
            'barcode_svg' => $this->makeSvgMarkup(
                $barcode->unique_code,
                $barcode->barcode_format?->value ?? $barcode->barcode_format,
                $barcode->barcode_data,
                $this->resolveDisplayLabel($barcode->custom_label, $barcode->unique_code)
            ),
            'is_active' => (bool) $barcode->is_active,
            'product' => $snapshot,
            'user' => $barcode->user ? [
                'id' => $barcode->user->id,
                'name' => $barcode->user->name,
                'email' => $barcode->user->email,
            ] : null,
            'created_at' => $barcode->created_at?->toISOString(),
            'updated_at' => $barcode->updated_at?->toISOString(),
            'scan_count' => (int) ($barcode->scan_logs_count ?? 0),
            'last_scanned_at' => $barcode->scan_logs_max_created_at ? Carbon::parse($barcode->scan_logs_max_created_at)->toISOString() : null,
        ], 'Barcode loaded successfully.');
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
            'barcode_format' => ['required', 'in:code128,code39,ean13'],
            'custom_label' => ['nullable', 'string', 'max:255'],
        ]);

        $uniqueCode = $this->generateUniqueCode();
        $format = $validated['barcode_format'];
        $barcodePayload = $this->resolveBarcodePayload($uniqueCode, $format, $validated['barcode_data']);

        $label = $this->resolveDisplayLabel($validated['custom_label'] ?? null, $uniqueCode);
        [$pngBinary, $svgMarkup] = $this->makeBarcodeAssets($barcodePayload, $format, $label);

        $barcodePath = 'barcodes/' . $uniqueCode . '.png';
        Storage::disk('public')->put($barcodePath, $pngBinary);

        $barcode = BarcodeGeneration::query()->create([
            'user_id' => $request->user()?->id,
            'unique_code' => $uniqueCode,
            'barcode_format' => $format,
            'barcode_data' => $validated['barcode_data'],
            'barcode_image_path' => $barcodePath,
            'public_url' => BarcodeGeneration::publicUrlForCode($uniqueCode),
            'custom_label' => $validated['custom_label'] ?? null,
            'is_active' => true,
        ]);

        return $this->successResponse([
            'unique_code' => $barcode->unique_code,
            'public_url' => $barcode->public_url ?? BarcodeGeneration::publicUrlForCode($barcode->unique_code),
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'barcode_image_base64' => base64_encode($pngBinary),
            'barcode_svg' => $svgMarkup,
            'barcode_image_url' => Storage::disk('public')->url($barcodePath),
            'custom_label' => $barcode->custom_label,
            'barcode_data' => $barcode->barcode_data,
            'created_at' => $barcode->created_at?->toISOString(),
        ], 'Barcode generated successfully.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'custom_label' => ['nullable', 'string', 'max:255'],
        ]);

        $barcode = BarcodeGeneration::query()->whereKey($id)->firstOrFail();
        $barcode->fill([
            'custom_label' => $validated['custom_label'] ?? null,
        ])->save();

        return $this->successResponse([
            'id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
            'public_url' => $barcode->public_url ?? BarcodeGeneration::publicUrlForCode($barcode->unique_code),
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'custom_label' => $barcode->custom_label,
            'barcode_data' => $barcode->barcode_data,
            'product_name' => $barcode->resolvedProductSnapshot()['name'] ?? $barcode->barcode_data,
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
            $code = 'BC' . strtoupper(Str::random(6));
        } while (BarcodeGeneration::query()->where('unique_code', $code)->exists());

        return $code;
    }

    // private function generateUniqueCode(): string
    // {
    //     $appUrl = rtrim((string) config('app.url'), '/');

    //     do {
    //         $code = $appUrl . '/scan' . '/BC' . strtoupper(uniqid());
    //     } while (BarcodeGeneration::query()->where('unique_code', $code)->exists());

    //     return $code;
    // }

    private function resolveBarcodePayload(string $uniqueCode, string $format, string $barcodeData): string
    {
        if ($format === 'ean13') {
            $digits = preg_replace('/\D+/', '', $uniqueCode . $barcodeData) ?: '';
            $payload = substr($digits . '0000000000000', 0, 12);

            return $payload;
        }

        return BarcodeGeneration::publicUrlForCode($uniqueCode);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function makeBarcodeAssets(string $payload, string $format, ?string $label = null): array
    {
        $pngGenerator = new BarcodeGeneratorPNG();
        $svgGenerator = new BarcodeGeneratorSVG();
        $type = $this->mapFormatToPicqerType($format);

        $png = $pngGenerator->getBarcode($payload, $type, 4, 120);
        $svg = $svgGenerator->getBarcode($payload, $type, 4, 120);

        $label = trim((string) $label);
        if ($label !== '') {
            $png = $this->appendLabelToPng($png, $label);
            $svg = $this->appendLabelToSvg($svg, $label);
        }

        return [$png, $svg];
    }

    private function makeSvgMarkup(string $uniqueCode, ?string $format, string $barcodeData, ?string $label = null): string
    {
        $payload = $this->resolveBarcodePayload($uniqueCode, $format ?? 'code128', $barcodeData);
        [, $svg] = $this->makeBarcodeAssets($payload, $format ?? 'code128', $label);

        return $svg;
    }

    private function resolveDisplayLabel(?string $customLabel, string $uniqueCode): string
    {
        $label = trim((string) $customLabel);

        return $label !== '' ? $label : $uniqueCode;
    }

    private function appendLabelToPng(string $pngBinary, string $label): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return $pngBinary;
        }

        $source = @imagecreatefromstring($pngBinary);
        if (! $source) {
            return $pngBinary;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $paddingX = 18;
        $paddingTop = 14;
        $paddingBottom = 18;
        $fontPath = 'C:\Windows\Fonts\arial.ttf';
        $useTrueType = is_file($fontPath) && function_exists('imagettfbbox') && function_exists('imagettftext');

        $bbox = $useTrueType ? imagettfbbox(20, 0, $fontPath, $label) : null;
        $labelBoxWidth = $useTrueType
            ? (int) abs(($bbox[4] ?? 0) - ($bbox[0] ?? 0))
            : imagefontwidth(5) * strlen($label);
        $labelHeight = $useTrueType ? 26 : imagefontheight(5);
        $canvasWidth = max($width, $labelBoxWidth + ($paddingX * 2));
        $canvasHeight = $height + $labelHeight + $paddingTop + $paddingBottom;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        imagefill($canvas, 0, 0, $white);

        $x = (int) floor(($canvasWidth - $width) / 2);
        imagecopy($canvas, $source, $x, 0, 0, 0, $width, $height);

        if ($useTrueType) {
            $textWidth = (int) abs(($bbox[4] ?? 0) - ($bbox[0] ?? 0));
            $textX = (int) floor(($canvasWidth - $textWidth) / 2);
            $textY = $height + $paddingTop + 22;
            imagettftext($canvas, 20, 0, $textX, $textY, $black, $fontPath, $label);
        } else {
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($label);
            imagestring($canvas, $font, (int) floor(($canvasWidth - $textWidth) / 2), $height + $paddingTop, $label, $black);
        }

        ob_start();
        imagepng($canvas);
        $output = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $output ?: $pngBinary;
    }

    private function appendLabelToSvg(string $svgMarkup, string $label): string
    {
        if (! preg_match('/<svg\\b[^>]*>/i', $svgMarkup, $matches, PREG_OFFSET_CAPTURE)) {
            return $svgMarkup;
        }

        $width = $this->extractSvgDimension($svgMarkup, 'width');
        $height = $this->extractSvgDimension($svgMarkup, 'height');
        $labelHeight = 32;
        $totalHeight = $height ? $height + $labelHeight : null;

        $svgMarkup = preg_replace_callback('/<svg\\b([^>]*)>/i', function (array $m) use ($totalHeight) {
            $attrs = $m[1];
            if ($totalHeight !== null) {
                if (preg_match('/\\bheight="[^"]*"/i', $attrs)) {
                    $attrs = preg_replace('/\\bheight="[^"]*"/i', 'height="' . $totalHeight . '"', $attrs);
                } else {
                    $attrs .= ' height="' . $totalHeight . '"';
                }
            }

            return '<svg' . $attrs . '>';
        }, $svgMarkup, 1);

        $labelX = $width ? (int) floor($width / 2) : 0;
        $labelY = $height ? $height + 24 : 24;
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $labelNode = '<text x="' . $labelX . '" y="' . $labelY . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="#000">' . $label . '</text>';

        return substr_replace($svgMarkup, $labelNode, $matches[0][1] + strlen($matches[0][0]), 0);
    }

    private function extractSvgDimension(string $svgMarkup, string $attribute): ?int
    {
        if (! preg_match('/' . preg_quote($attribute, '/') . '="([^"]+)"/i', $svgMarkup, $matches)) {
            return null;
        }

        $value = (string) $matches[1];
        if (preg_match('/^(\d+(?:\.\d+)?)/', $value, $numberMatches)) {
            return (int) round((float) $numberMatches[1]);
        }

        return null;
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





















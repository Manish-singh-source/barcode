<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BarcodeGeneration;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
            'barcode_format' => $barcode->barcode_format?->value ?? $barcode->barcode_format,
            'barcode_data' => $barcode->barcode_data,
            'custom_label' => $barcode->custom_label,
            'barcode_image_url' => $barcode->barcode_image_path ? Storage::disk('public')->url($barcode->barcode_image_path) : null,
            'barcode_image_path' => $barcode->barcode_image_path,
            'barcode_svg' => $this->makeSvgMarkup($barcode->unique_code, $barcode->barcode_format?->value ?? $barcode->barcode_format, $barcode->barcode_data),
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
            'barcode_format' => ['required', 'in:code128,qrcode,code39,ean13'],
            'custom_label' => ['nullable', 'string', 'max:255'],
        ]);

        $uniqueCode = $this->generateUniqueCode();
        $format = $validated['barcode_format'];
        $barcodePayload = $this->resolveBarcodePayload($uniqueCode, $format, $validated['barcode_data']);

        [$pngBinary, $svgMarkup] = $this->makeBarcodeAssets($barcodePayload, $format);

        $barcodePath = 'barcodes/' . $uniqueCode . '.png';
        Storage::disk('public')->put($barcodePath, $pngBinary);

        $barcode = BarcodeGeneration::query()->create([
            'user_id' => $request->user()?->id,
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
        ]);

        $barcode = BarcodeGeneration::query()->whereKey($id)->firstOrFail();
        $barcode->fill([
            'custom_label' => $validated['custom_label'] ?? null,
        ])->save();

        return $this->successResponse([
            'id' => $barcode->id,
            'unique_code' => $barcode->unique_code,
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

    private function makeSvgMarkup(string $uniqueCode, ?string $format, string $barcodeData): string
    {
        $payload = $this->resolveBarcodePayload($uniqueCode, $format ?? 'code128', $barcodeData);
        [, $svg] = $this->makeBarcodeAssets($payload, $format ?? 'code128');

        return $svg;
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
<?php

namespace App\Http\Controllers;

use App\Models\BarcodeGeneration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeWebController extends Controller
{
    public function index(): View
    {
        $barcodes = BarcodeGeneration::query()
            ->with('user')
            ->latest()
            ->get();

        return view('admin.barcodes.index', compact('barcodes'));
    }

    public function show(int $id): View
    {
        $barcode = BarcodeGeneration::query()
            ->with('user')
            ->withCount('scanLogs')
            ->withMax('scanLogs', 'created_at')
            ->findOrFail($id);

        $barcode->barcode_svg = $this->makeSvgMarkup($barcode->unique_code, $barcode->barcode_format?->value ?? $barcode->barcode_format, (string) $barcode->barcode_data);

        return view('admin.barcodes.show', compact('barcode'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'custom_label' => ['nullable', 'string', 'max:255'],
        ]);

        $barcode = BarcodeGeneration::query()->findOrFail($id);
        $barcode->fill([
            'custom_label' => $validated['custom_label'] ?? null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Barcode updated successfully.',
                'data' => [
                    'id' => $barcode->id,
                    'custom_label' => $barcode->custom_label,
                ],
            ]);
        }

        return redirect()
            ->route('barcodes.index')
            ->with('success', 'Barcode updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $barcode = BarcodeGeneration::query()->findOrFail($id);
        $barcode->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Barcode deleted successfully.',
            ]);
        }

        return redirect()
            ->route('barcodes.index')
            ->with('success', 'Barcode deleted successfully.');
    }

    private function makeSvgMarkup(string $uniqueCode, ?string $format, string $barcodeData): string
    {
        $payload = $format === 'ean13'
            ? substr(preg_replace('/\D+/', '', $uniqueCode . $barcodeData) . '0000000000000', 0, 12)
            : $uniqueCode;

        $generator = new BarcodeGeneratorSVG();
        $type = match ($format) {
            'code39' => BarcodeGeneratorSVG::TYPE_CODE_39,
            'ean13' => BarcodeGeneratorSVG::TYPE_EAN_13,
            'qrcode' => BarcodeGeneratorSVG::TYPE_CODE_128,
            default => BarcodeGeneratorSVG::TYPE_CODE_128,
        };

        return $generator->getBarcode($payload, $type, 3, 100);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BarcodeGeneration;
use Illuminate\Http\RedirectResponse;
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

    public function publicShow(Request $request, string $unique_code): View|RedirectResponse
    {
        $barcode = BarcodeGeneration::query()
            ->with('user')
            ->where('unique_code', $unique_code)
            ->firstOrFail();

        $shortHost = parse_url(config('barcode.short_url_base', 'https://wpc.bar'), PHP_URL_HOST);

        if ($shortHost && strcasecmp($request->getHost(), $shortHost) === 0) {
            $longBase = rtrim((string) config('app.url', 'https://wpc.bar'), '/');

            return redirect()->away($longBase . '/b/' . $unique_code);
        }

        return view('public.barcode-show', compact('barcode'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'barcode_data' => ['nullable', 'string', 'max:5000'],
        ]);

        $barcode = BarcodeGeneration::query()->findOrFail($id);
        $barcode->fill([
            'barcode_data' => $validated['barcode_data'] ?? null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Barcode data updated successfully.',
                'data' => [
                    'id' => $barcode->id,
                    'barcode_data' => $barcode->barcode_data,
                ],
            ]);
        }

        return redirect()
            ->route('barcodes.index')
            ->with('success', 'Barcode data updated successfully.');
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


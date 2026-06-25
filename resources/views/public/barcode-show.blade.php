<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
        }
        .code-pill {
            letter-spacing: .08em;
        }
    </style>
</head>
<body class="d-flex align-items-center py-5">
    @php
        $barcodeData = \App\Models\BarcodeGeneration::normalizeText($barcode->barcode_data);
    @endphp
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <div class="text-uppercase text-secondary small fw-semibold">Public Barcode</div>
                                <h1 class="h3 fw-bold mb-0">{{ $barcode->custom_label ?: $barcode->unique_code }}</h1>
                            </div>
                            <span class="badge text-bg-dark code-pill">{{ $barcode->unique_code }}</span>
                        </div>

                        <div class="mb-4">
                            <div class="text-secondary small mb-1">Barcode Data</div>
                            <div class="fs-5 fw-semibold text-break">{{ $barcodeData }}</div>
                        </div>

                        <div class="mb-4">
                            <div class="text-secondary small mb-1">Public Link</div>
                            <a class="text-break" href="{{ route('barcodes.public-show', $barcode->unique_code) }}" target="_blank" rel="noopener">{{ route('barcodes.public-show', $barcode->unique_code) }}</a>
                        </div>

                        {{-- <div class="d-flex flex-wrap gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Go Back</a>
                            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                        </div> --}}
                    </div>
                </div>
                <div class="text-center text-secondary small mt-3">Scan result from the barcode_generations table</div>
            </div>
        </div>
    </div>
</body>
</html>

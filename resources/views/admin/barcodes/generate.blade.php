@extends('layouts.admin')

@section('content')
<div class="row align-items-start g-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2">
            <div>
                <h1 class="h3 fw-bold mb-1">Generate Barcode</h1>
                <p class="text-secondary mb-0">Create a printable barcode or QR code from raw barcode data.</p>
            </div>
            <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100 rounded-4">
            <div class="card-body p-4 p-xl-5">
                <form id="generateBarcodeForm" class="vstack gap-3" novalidate>
                    @csrf
                    <div id="generateError" class="alert alert-danger d-none" role="alert"></div>

                    <div>
                        <label for="barcodeData" class="form-label fw-semibold">Barcode Content</label>
                        <textarea id="barcodeData" class="form-control form-control-lg" rows="4" required placeholder="Enter product name, SKU, description, or any text..."></textarea>
                        <div id="duplicateStatus" class="small mt-2"></div>
                    </div>

                    <div>
                        <label for="customLabel" class="form-label fw-semibold">Human Readable Label (shown below barcode)</label>
                        <input type="text" id="customLabel" class="form-control form-control-lg" placeholder="e.g. PROD-001">
                    </div>

                    <div>
                        <label for="barcodeFormat" class="form-label fw-semibold">Barcode Format</label>
                        <select id="barcodeFormat" class="form-select form-select-lg">
                            <option value="code128" selected>Code 128 - Default</option>
                            <option value="qrcode">QR Code</option>
                            <option value="code39">Code 39</option>
                            <option value="ean13">EAN-13</option>
                        </select>
                    </div>

                    <button type="submit" id="generateBtn" class="btn btn-primary btn-lg w-100">
                        <span class="btn-label">Generate Barcode</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div id="previewCard" class="card border-2 border-dashed border-secondary-subtle bg-light-subtle shadow-sm rounded-4 h-100">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-bold mb-1">Preview</h2>
                        <p class="text-secondary mb-0">Your barcode will appear here once it is generated.</p>
                    </div>
                    <div class="badge text-bg-light border">Live Preview</div>
                </div>

                <div id="previewSkeleton" class="placeholder-glow">
                    <div class="d-flex justify-content-center py-4">
                        <div class="placeholder bg-secondary-subtle rounded-3" style="width: 100%; max-width: 440px; height: 220px;"></div>
                    </div>
                    <div class="placeholder col-8 rounded-pill d-block mb-3" style="height: 20px;"></div>
                    <div class="placeholder col-5 rounded-pill d-block" style="height: 16px;"></div>
                </div>

                <div id="previewContent" class="d-none">
                    <div class="text-center">
                        <img id="barcodePreview" alt="Generated barcode preview" class="img-fluid rounded-3 bg-white border p-3" style="max-width: 100%; display: none;">
                    </div>

                    <div class="mt-4 text-center">
                        <div id="humanReadableText" class="fs-5 fw-semibold mb-2"></div>
                        <div id="uniqueCodeDisplay" class="text-secondary d-none"></div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                        <button type="button" id="downloadPng" class="btn btn-dark d-none">Download PNG</button>
                        <button type="button" id="downloadSvg" class="btn btn-outline-dark d-none">Download SVG</button>
                        <a href="#" id="generateAnother" class="btn btn-link d-none text-decoration-none">Generate another</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-dashed {
        border-style: dashed !important;
    }

    .bg-light-subtle {
        background: linear-gradient(180deg, rgba(241, 245, 249, 0.92), rgba(255, 255, 255, 0.98));
    }

    #previewCard.is-ready {
        background: #fff;
        border-style: solid !important;
    }

    #duplicateStatus.is-valid {
        color: #198754;
    }

    #duplicateStatus.is-warning {
        color: #b45309;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('generateBarcodeForm');
        const barcodeData = document.getElementById('barcodeData');
        const customLabel = document.getElementById('customLabel');
        const barcodeFormat = document.getElementById('barcodeFormat');
        const generateBtn = document.getElementById('generateBtn');
        const generateError = document.getElementById('generateError');
        const duplicateStatus = document.getElementById('duplicateStatus');
        const previewCard = document.getElementById('previewCard');
        const previewSkeleton = document.getElementById('previewSkeleton');
        const previewContent = document.getElementById('previewContent');
        const barcodePreview = document.getElementById('barcodePreview');
        const humanReadableText = document.getElementById('humanReadableText');
        const uniqueCodeDisplay = document.getElementById('uniqueCodeDisplay');
        const downloadPng = document.getElementById('downloadPng');
        const downloadSvg = document.getElementById('downloadSvg');
        const generateAnother = document.getElementById('generateAnother');
        const btnLabel = generateBtn.querySelector('.btn-label');

        let duplicateTimer = null;
        let currentPngBase64 = '';
        let currentSvg = '';
        let currentUniqueCode = '';

        function setError(message) {
            if (!message) {
                generateError.classList.add('d-none');
                generateError.textContent = '';
                return;
            }

            generateError.textContent = message;
            generateError.classList.remove('d-none');
        }

        function setDuplicateState(type, message) {
            duplicateStatus.className = 'small mt-2 ' + type;
            duplicateStatus.innerHTML = message || '';
        }

        function setGenerating(isGenerating) {
            generateBtn.disabled = isGenerating;
            btnLabel.innerHTML = isGenerating
                ? '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Generating...'
                : 'Generate Barcode';
        }

        function resetPreview() {
            previewCard.classList.remove('is-ready');
            previewSkeleton.classList.remove('d-none');
            previewContent.classList.add('d-none');
            barcodePreview.style.display = 'none';
            barcodePreview.removeAttribute('src');
            humanReadableText.textContent = '';
            uniqueCodeDisplay.classList.add('d-none');
            uniqueCodeDisplay.textContent = '';
            downloadPng.classList.add('d-none');
            downloadSvg.classList.add('d-none');
            generateAnother.classList.add('d-none');
            currentPngBase64 = '';
            currentSvg = '';
            currentUniqueCode = '';
        }

        function showPreview(response) {
            currentPngBase64 = response.barcode_image_base64 || '';
            currentSvg = response.barcode_svg || '';
            currentUniqueCode = response.unique_code || '';

            previewCard.classList.add('is-ready');
            previewSkeleton.classList.add('d-none');
            previewContent.classList.remove('d-none');

            barcodePreview.src = 'data:image/png;base64,' + currentPngBase64;
            barcodePreview.style.display = 'inline-block';
            humanReadableText.textContent = response.custom_label || response.unique_code || '';
            uniqueCodeDisplay.textContent = 'Unique Code: ' + currentUniqueCode;
            uniqueCodeDisplay.classList.remove('d-none');
            downloadPng.classList.remove('d-none');
            downloadSvg.classList.remove('d-none');
            generateAnother.classList.remove('d-none');
        }

        function triggerDownload(href, filename) {
            const link = document.createElement('a');
            link.href = href;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        async function checkDuplicate() {
            const value = barcodeData.value.trim();
            if (!value) {
                setDuplicateState('', '');
                return;
            }

            setDuplicateState('text-secondary', '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking for duplicates...');

            try {
                const response = await fetch('/api/v1/barcodes/check-duplicate?data=' + encodeURIComponent(value), {
                    headers: setAuthHeaders(),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    setDuplicateState('text-warning', '&#9888; Unable to validate duplicate status right now.');
                    return;
                }

                const exists = Boolean(payload.data?.exists);

                if (exists) {
                    setDuplicateState('is-warning', '&#9888; Similar data exists, a new unique code will still be generated.');
                    return;
                }

                setDuplicateState('is-valid', '&#10003; Unique');
            } catch (error) {
                setDuplicateState('text-warning', '&#9888; Unable to validate duplicate status right now.');
            }
        }

        barcodeData.addEventListener('input', function () {
            window.clearTimeout(duplicateTimer);
            duplicateTimer = window.setTimeout(checkDuplicate, 800);
        });

        downloadPng.addEventListener('click', function () {
            if (!currentPngBase64 || !currentUniqueCode) {
                return;
            }

            triggerDownload('data:image/png;base64,' + currentPngBase64, currentUniqueCode + '.png');
        });

        downloadSvg.addEventListener('click', function () {
            if (!currentSvg || !currentUniqueCode) {
                return;
            }

            const blob = new Blob([currentSvg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            triggerDownload(url, currentUniqueCode + '.svg');
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        });

        generateAnother.addEventListener('click', function (event) {
            event.preventDefault();
            form.reset();
            setError('');
            setDuplicateState('', '');
            resetPreview();
            barcodeData.focus();
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            setError('');

            const barcodeValue = barcodeData.value.trim();
            if (!barcodeValue) {
                setError('Barcode content is required.');
                return;
            }

            setGenerating(true);

            try {
                const response = await fetch('/api/v1/barcodes/generate', {
                    method: 'POST',
                    headers: setAuthHeaders(),
                    body: JSON.stringify({
                        barcode_data: barcodeValue,
                        barcode_format: barcodeFormat.value,
                        custom_label: customLabel.value.trim() || null,
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    setError(getApiErrorMessage(payload, 'Unable to generate barcode.'));
                    return;
                }

                showPreview(payload.data || {});
            } catch (error) {
                setError('Unable to generate barcode right now.');
            } finally {
                setGenerating(false);
            }
        });

        resetPreview();
    })();
</script>
@endpush
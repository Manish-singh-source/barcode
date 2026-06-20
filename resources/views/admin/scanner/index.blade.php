@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Barcode Scanner</h1>
        <p class="text-secondary mb-0">Scan barcodes to look up product details.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 p-xl-5">
                <div id="cameraFrame" class="scanner-frame rounded-4 p-3 mb-4 border border-2 border-dashed border-secondary-subtle">
                    <video id="cameraPreview" class="w-100 rounded-3" autoplay muted playsinline style="max-height: 300px; background: #000; object-fit: cover;"></video>
                    <div id="scannerReader" class="visually-hidden"></div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button type="button" id="startCameraBtn" class="btn btn-success">Start Camera</button>
                    <button type="button" id="stopCameraBtn" class="btn btn-secondary d-none">Stop Camera</button>
                </div>

                <div class="text-center text-secondary my-4 position-relative">
                    <span class="px-3 bg-white position-relative z-1">OR</span>
                    <div class="position-absolute top-50 start-0 end-0 border-top"></div>
                </div>

                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-9">
                        <label for="manualBarcodeInput" class="form-label fw-semibold">Manual Barcode</label>
                        <input type="text" id="manualBarcodeInput" class="form-control form-control-lg" placeholder="Enter barcode or unique code">
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="button" id="manualLookupBtn" class="btn btn-primary btn-lg">Lookup</button>
                    </div>
                </div>

                <div class="text-center text-secondary my-4 position-relative">
                    <span class="px-3 bg-white position-relative z-1">OR</span>
                    <div class="position-absolute top-50 start-0 end-0 border-top"></div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label for="uploadFileInput" class="form-label fw-semibold">Upload Barcode Image</label>
                        <input type="file" id="uploadFileInput" class="form-control form-control-lg" accept="image/*">
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="button" id="scanFileBtn" class="btn btn-outline-primary btn-lg">Scan File</button>
                    </div>
                </div>

                <div id="scannerStatus" class="mt-4"></div>
            </div>
        </div>

        <div id="resultCard" class="card border-0 shadow-sm rounded-4 mt-4 d-none">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4 px-xl-5">
                <h2 class="h5 fw-bold mb-0">Scan Result</h2>
                <button type="button" id="copyAllBtn" class="btn btn-sm btn-outline-dark"><i class="bi bi-copy me-1"></i>Copy All</button>
            </div>
            <div id="resultBorder" class="card-body p-4 p-xl-5 border-start border-4 border-success">
                <div id="invalidAlert" class="alert alert-danger d-none">Invalid barcode - no product found</div>
                <div id="resultRows" class="vstack gap-2"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Scan History (This Session)</h2>
                        <div class="small text-secondary">Stored in this browser only.</div>
                    </div>
                    <button type="button" id="clearHistoryBtn" class="btn btn-sm btn-outline-secondary">Clear All</button>
                </div>

                <div id="historyEmpty" class="alert alert-light border mb-0">No scans yet in this session.</div>
                <div id="historyList" class="list-group list-group-flush"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .scanner-frame.is-active {
        border-style: solid !important;
        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.08);
        animation: scannerPulse 1.6s ease-in-out infinite;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    #resultBorder.is-success {
        border-color: #198754 !important;
    }

    #resultBorder.is-danger {
        border-color: #dc3545 !important;
    }

    #historyList .list-group-item {
        border-left: 0;
        border-right: 0;
        padding-left: 0;
        padding-right: 0;
        background: transparent;
    }

    #historyList .history-row {
        background: rgba(15, 23, 42, 0.03);
        border-radius: 0.85rem;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    #historyList .history-row:hover {
        background: rgba(15, 23, 42, 0.06);
        transform: translateY(-1px);
    }

    @keyframes scannerPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.12); }
        50% { box-shadow: 0 0 0 8px rgba(25, 135, 84, 0.02); }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    (function () {
        const historyKey = 'admin_scan_history';
        const cameraFrame = document.getElementById('cameraFrame');
        const cameraPreview = document.getElementById('cameraPreview');
        const scannerReader = document.getElementById('scannerReader');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const stopCameraBtn = document.getElementById('stopCameraBtn');
        const manualBarcodeInput = document.getElementById('manualBarcodeInput');
        const manualLookupBtn = document.getElementById('manualLookupBtn');
        const uploadFileInput = document.getElementById('uploadFileInput');
        const scanFileBtn = document.getElementById('scanFileBtn');
        const scannerStatus = document.getElementById('scannerStatus');
        const resultCard = document.getElementById('resultCard');
        const resultBorder = document.getElementById('resultBorder');
        const invalidAlert = document.getElementById('invalidAlert');
        const resultRows = document.getElementById('resultRows');
        const copyAllBtn = document.getElementById('copyAllBtn');
        const historyEmpty = document.getElementById('historyEmpty');
        const historyList = document.getElementById('historyList');
        const clearHistoryBtn = document.getElementById('clearHistoryBtn');

        let mediaStream = null;
        let html5Qrcode = null;
        let scannerRunning = false;
        let scanInProgress = false;
        let currentResultText = '';

        function authHeaders() {
            return {
                Authorization: 'Bearer ' + localStorage.getItem('auth_token'),
                Accept: 'application/json',
                'Content-Type': 'application/json',
            };
        }

        function getHistory() {
            try {
                return JSON.parse(localStorage.getItem(historyKey) || '[]');
            } catch (error) {
                return [];
            }
        }

        function setHistory(items) {
            localStorage.setItem(historyKey, JSON.stringify(items.slice(0, 20)));
            renderHistory();
        }

        function pushHistory(uniqueCode, resultText, status) {
            const items = getHistory().filter(item => item.unique_code !== uniqueCode || item.result_text !== resultText || item.status !== status);
            items.unshift({
                unique_code: uniqueCode,
                result_text: resultText,
                status: status,
                timestamp: new Date().toISOString(),
            });
            setHistory(items);
        }

        function renderHistory() {
            const items = getHistory().slice(0, 10);
            historyList.innerHTML = '';
            historyEmpty.classList.toggle('d-none', items.length !== 0);

            items.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'list-group-item';
                row.innerHTML = `
                    <div class="history-row p-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-semibold">${item.unique_code}</div>
                                <div class="small text-secondary">${new Date(item.timestamp).toLocaleString()}</div>
                                <div class="small ${item.status === 'Invalid' ? 'text-danger' : 'text-success'}">${item.status || 'Found'}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-dark" data-action="copy" data-index="${index}">Copy</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-index="${index}">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
                historyList.appendChild(row);
            });
        }

        function setStatus(message, type = 'info') {
            scannerStatus.innerHTML = message ? `<div class="alert alert-${type} mb-0">${message}</div>` : '';
        }

        function setCameraActive(active) {
            cameraFrame.classList.toggle('is-active', active);
            cameraFrame.classList.toggle('border-dashed', !active);
            startCameraBtn.classList.toggle('d-none', active);
            stopCameraBtn.classList.toggle('d-none', !active);
        }

        async function startPreview() {
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                cameraPreview.srcObject = mediaStream;
                await cameraPreview.play();
            } catch (error) {
                setStatus('Camera permission was denied or not available.', 'danger');
            }
        }

        function stopPreview() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }

            cameraPreview.srcObject = null;
        }

        function setResultState(success) {
            resultCard.classList.remove('d-none');
            resultBorder.classList.toggle('is-success', success);
            resultBorder.classList.toggle('is-danger', !success);
            invalidAlert.classList.toggle('d-none', success);
        }

                function renderResult(data) {
            const product = data.product || {};
            const rows = [
                ['Unique Code', data.unique_code],
                ['Barcode Format', data.barcode_format || 'N/A'],
                ['Custom Label', data.custom_label || 'N/A'],
                ['Product Name', product.name || 'N/A'],
                ['Description', product.description || 'N/A'],
                ['SKU', product.sku || 'N/A'],
                ['Price', product.price ?? 'N/A'],
                ['Brand', product.brand || 'N/A'],
                ['Category', product.category || 'N/A'],
                ['Unit', product.unit || 'N/A'],
                ['Stock Quantity', product.stock_quantity ?? 'N/A'],
                ['Scanned At', data.scanned_at ? new Date(data.scanned_at).toLocaleString() : new Date().toLocaleString()],
            ];

            currentResultText = rows.map(([label, value]) => `${label}: ${value}`).join('\n');
            resultRows.innerHTML = rows.map(([label, value]) => `
                <div class="d-flex justify-content-between gap-3 border-bottom pb-2">
                    <span class="text-secondary">${label}</span>
                    <span class="fw-semibold text-end">${value ?? 'N/A'}</span>
                </div>
            `).join('');
            setResultState(true);
        }

                function renderNotFound(uniqueCode) {
            currentResultText = `Unique Code: ${uniqueCode}\nStatus: Invalid`;
            resultRows.innerHTML = '';
            setResultState(false);
            pushHistory(uniqueCode, currentResultText, 'Invalid');
        }

                async function lookupBarcode(code) {
            const uniqueCode = (code || '').trim();
            if (!uniqueCode) {
                setStatus('Enter a barcode value first.', 'warning');
                return;
            }

            manualBarcodeInput.value = uniqueCode;
            setStatus('Looking up barcode...', 'secondary');

            try {
                const response = await fetch(`/api/v1/scan/${encodeURIComponent(uniqueCode)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const payload = await response.json().catch(() => ({}));

                if (!payload.data || !payload.data.valid) {
                    renderNotFound(uniqueCode);
                    setStatus(payload.message || 'Invalid barcode — no product found.', 'danger');
                    return;
                }

                setStatus('Barcode found.', 'success');
                renderResult(payload.data);
                pushHistory(uniqueCode, currentResultText, 'Found');
            } catch (error) {
                setStatus('Something went wrong while looking up the barcode.', 'danger');
            }
        }

        async function startCamera() {
            if (scannerRunning) {
                return;
            }

            if (!html5Qrcode) {
                html5Qrcode = new Html5Qrcode('scannerReader');
            }

            try {
                await startPreview();
                scannerRunning = true;
                setCameraActive(true);
                await html5Qrcode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.QR_CODE,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.EAN_13,
                        ],
                    },
                    async (decodedText) => {
                        const uniqueCode = (decodedText || '').trim();
                        if (!uniqueCode || scanInProgress) {
                            return;
                        }

                        scanInProgress = true;
                        manualBarcodeInput.value = uniqueCode;

                        try {
                            await lookupBarcode(uniqueCode);
                            await stopCamera(true);
                        } finally {
                            scanInProgress = false;
                        }
                    }
                );
            } catch (error) {
                scannerRunning = false;
                setCameraActive(false);
                stopPreview();
                setStatus('Unable to start the scanner.', 'danger');
            }
        }

        async function stopCamera(silent = false) {
            if (html5Qrcode && scannerRunning) {
                try {
                    await html5Qrcode.stop();
                } catch (error) {
                    // ignore stop failures
                }
                scannerRunning = false;
                html5Qrcode.clear().catch(() => {});
            }
            stopPreview();
            setCameraActive(false);
            if (!silent) {
                setStatus('Camera stopped.', 'secondary');
            }
        }

        async function scanFile() {
            const file = uploadFileInput.files[0];
            if (!file) {
                setStatus('Choose an image file to scan.', 'warning');
                return;
            }

            if (!html5Qrcode) {
                html5Qrcode = new Html5Qrcode('scannerReader');
            }

            try {
                const decodedText = (await html5Qrcode.scanFile(file, true)).trim();
                manualBarcodeInput.value = decodedText;
                await lookupBarcode(decodedText);
            } catch (error) {
                setStatus('No barcode could be read from that image.', 'danger');
            }
        }

        async function copyAll() {
            if (!currentResultText) {
                return;
            }

            await navigator.clipboard.writeText(currentResultText);
            setStatus('Result copied to clipboard.', 'success');
        }

        startCameraBtn.addEventListener('click', startCamera);
        stopCameraBtn.addEventListener('click', stopCamera);
        manualLookupBtn.addEventListener('click', () => lookupBarcode(manualBarcodeInput.value));
        manualBarcodeInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                lookupBarcode(manualBarcodeInput.value);
            }
        });
        scanFileBtn.addEventListener('click', scanFile);
        copyAllBtn.addEventListener('click', copyAll);
        clearHistoryBtn.addEventListener('click', () => {
            localStorage.removeItem(historyKey);
            renderHistory();
            setStatus('History cleared.', 'secondary');
        });

        historyList.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const items = getHistory();
            const index = Number(button.dataset.index);
            const item = items[index];

            if (!item) {
                return;
            }

            if (button.dataset.action === 'copy') {
                await navigator.clipboard.writeText(item.result_text);
                setStatus('History item copied.', 'success');
                return;
            }

            if (button.dataset.action === 'delete') {
                items.splice(index, 1);
                setHistory(items);
                setStatus('History item deleted.', 'secondary');
            }
        });

        setCameraActive(false);
        renderHistory();
    })();
</script>
@endpush






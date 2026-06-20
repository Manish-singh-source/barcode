@extends('layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded-4 px-3 px-md-4 mb-4 shadow-sm">
    <div class="container-fluid p-0">
        <a class="navbar-brand fw-bold fs-4" href="{{ url('/') }}">BarcodeMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="ms-auto d-grid gap-2 d-lg-flex pt-3 pt-lg-0">
                <a href="{{ url('/login') }}" class="btn btn-outline-light">Login</a>
                <a href="{{ url('/register') }}" class="btn btn-primary">Register</a>
            </div>
        </div>
    </div>
</nav>

<section class="py-5 py-lg-6 text-center text-lg-start">
    <div class="row align-items-center g-4 g-lg-5">
        <div class="col-lg-7">
            <span class="badge rounded-pill text-bg-dark px-3 py-2 mb-3">Public barcode lookup</span>
            <h1 class="display-4 fw-bold lh-1 mb-3">Scan Any Barcode Instantly</h1>
            <p class="lead text-secondary mb-4">No login required. Point your camera and get product details in seconds.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-center justify-content-lg-start">
                <a href="#scanner-section" class="btn btn-lg btn-dark px-4">Start Scanning</a>
                <a href="{{ url('/login') }}" class="btn btn-lg btn-outline-primary px-4">Admin Login</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="hero-card p-4 p-md-5 rounded-4 shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-uppercase small text-secondary fw-semibold">Live product access</span>
                    <span class="badge text-bg-success-subtle text-success border border-success-subtle">Fast</span>
                </div>
                <div class="display-6 fw-bold mb-2">Barcode + QR ready</div>
                <p class="text-secondary mb-0">Designed for fast field scanning, quick manual lookups, and clean history tracking in the browser.</p>
            </div>
        </div>
    </div>
</section>

<section id="scanner-section" class="py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 px-4 px-md-5 pt-4 pb-0">
                    <h2 class="h3 fw-bold mb-1">Barcode Scanner</h2>
                    <p class="text-secondary mb-0">Use the camera, enter a code manually, or upload an image to look up a product.</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="scanner-frame rounded-4 p-3 mb-4 bg-dark-subtle border border-2 border-secondary-subtle">
                        <video id="camera-preview" class="w-100 rounded-3 bg-dark landing-camera-preview" autoplay muted playsinline></video>
                        <div id="scanner-reader" class="visually-hidden"></div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex mb-4 scanner-actions">
                        <button type="button" id="start-camera-btn" class="btn btn-dark">Start Camera</button>
                        <button type="button" id="stop-camera-btn" class="btn btn-outline-danger">Stop Camera</button>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label for="manual-code" class="form-label fw-semibold">Or enter barcode manually</label>
                            <div class="input-group input-group-responsive">
                                <input type="text" id="manual-code" class="form-control form-control-lg" placeholder="Enter unique code">
                                <button type="button" id="lookup-btn" class="btn btn-primary">Lookup</button>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="barcode-file" class="form-label fw-semibold">Or upload an image of the barcode</label>
                            <div class="input-group input-group-responsive">
                                <input type="file" id="barcode-file" class="form-control form-control-lg" accept="image/*">
                                <button type="button" id="scan-file-btn" class="btn btn-outline-primary">Scan File</button>
                            </div>
                        </div>
                    </div>

                    <div id="scanner-status" class="mt-4"></div>

                    <div id="result-wrap" class="mt-4 d-none">
                        <div class="card border-success-subtle bg-success-subtle">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-4">Result</span>
                                        <h3 class="h5 mb-0 fw-bold">Scan Result</h3>
                                    </div>
                                    <button type="button" id="copy-result-btn" class="btn btn-sm btn-outline-dark">Copy</button>
                                </div>
                                <div id="result-content" class="vstack gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <div id="history-alert" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Scan History</h2>
                            <p class="text-secondary mb-0">Last 10 scans from this browser.</p>
                        </div>
                        <button type="button" id="clear-history-btn" class="btn btn-outline-secondary">Clear All</button>
                    </div>
                    <div id="history-empty" class="alert alert-light border mb-0 d-none">No scan history yet.</div>
                    <div id="history-list" class="list-group list-group-flush"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-4 text-center text-secondary">
    <small>&copy; {{ date('Y') }} BarcodeMS. All rights reserved.</small>
</footer>
@endsection

@push('styles')
<style>
    .hero-card {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.10), rgba(33, 37, 41, 0.06));
        border: 1px solid rgba(13, 110, 253, 0.14);
    }

    .scanner-frame {
        min-height: 360px;
    }

    .landing-camera-preview {
        min-height: 320px;
        object-fit: cover;
    }

    #history-list .list-group-item {
        border-left: 0;
        border-right: 0;
        padding-left: 0;
        padding-right: 0;
    }

    @media (max-width: 575.98px) {
        .display-4 {
            font-size: 2.1rem;
        }

        .lead {
            font-size: 1rem;
        }

        .hero-card {
            padding: 1.25rem !important;
        }

        .scanner-frame {
            min-height: 240px;
        }

        .landing-camera-preview {
            min-height: 240px;
        }

        .scanner-actions .btn,
        .input-group-responsive > .btn,
        .input-group-responsive > .form-control,
        .navbar .btn {
            width: 100%;
        }

        .input-group-responsive {
            flex-direction: column;
        }

        .input-group-responsive > .form-control,
        .input-group-responsive > .btn {
            border-radius: 0.75rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const historyKey = 'scan_history';
    const video = document.getElementById('camera-preview');
    const scannerStatus = document.getElementById('scanner-status');
    const historyAlert = document.getElementById('history-alert');
    const resultWrap = document.getElementById('result-wrap');
    const resultContent = document.getElementById('result-content');
    const historyList = document.getElementById('history-list');
    const historyEmpty = document.getElementById('history-empty');
    const manualCode = document.getElementById('manual-code');
    const barcodeFile = document.getElementById('barcode-file');
    const startCameraBtn = document.getElementById('start-camera-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const lookupBtn = document.getElementById('lookup-btn');
    const scanFileBtn = document.getElementById('scan-file-btn');
    const clearHistoryBtn = document.getElementById('clear-history-btn');
    const copyResultBtn = document.getElementById('copy-result-btn');

    let mediaStream = null;
    let html5Qrcode = null;
    let lastResultText = '';
    let scannerRunning = false;

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

    function pushHistory(uniqueCode, resultText, valid) {
        const items = getHistory().filter(item => item.unique_code !== uniqueCode || item.result_text !== resultText || item.valid !== valid);
        items.unshift({
            unique_code: uniqueCode,
            result_text: resultText,
            valid: valid,
            timestamp: new Date().toISOString()
        });
        setHistory(items);
    }

    function formatTimestamp(value) {
        return new Date(value).toLocaleString();
    }

    function renderHistory() {
        const items = getHistory().slice(0, 10);
        historyList.innerHTML = '';
        historyEmpty.classList.toggle('d-none', items.length !== 0);

        items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'list-group-item';
            row.innerHTML = `
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 py-2">
                    <div>
                        <div class="fw-semibold">${item.unique_code}</div>
                        <div class="small text-secondary">${formatTimestamp(item.timestamp)}</div>
                        <div class="small ${item.valid ? 'text-success' : 'text-danger'}">${item.valid ? 'Found' : 'Invalid'}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" data-action="copy" data-index="${index}">Copy</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-index="${index}">Delete</button>
                    </div>
                </div>
            `;
            historyList.appendChild(row);
        });
    }

    function setStatus(message, type = 'info') {
        scannerStatus.innerHTML = message ? `<div class="alert alert-${type} mb-0">${message}</div>` : '';
    }

    function setHistoryMessage(message, type = 'warning') {
        historyAlert.innerHTML = message ? `<div class="alert alert-${type} mb-0">${message}</div>` : '';
        if (message) {
            window.setTimeout(() => {
                historyAlert.innerHTML = '';
            }, 3500);
        }
    }

    function sanitizeText(value) {
        return (value ?? '').toString().replace(/\uFFFD/g, '').trim();
    }

    function renderResult(data) {
        const product = data.product || {};
        const rows = [
            ['Unique Code', data.unique_code],
            ['Barcode Format', data.barcode_format || 'N/A'],
            ['Custom Label', sanitizeText(data.custom_label) || 'N/A'],
            ['Product Name', sanitizeText(product.name) || 'N/A'],
            ['Description', product.description || 'N/A'],
            ['SKU', product.sku || 'N/A'],
            ['Price', product.price ?? 'N/A'],
            ['Brand', product.brand || 'N/A'],
            ['Category', product.category || 'N/A'],
            ['Unit', product.unit || 'N/A'],
            ['Stock Quantity', product.stock_quantity ?? 'N/A'],
            ['Scanned At', data.scanned_at ? new Date(data.scanned_at).toLocaleString() : new Date().toLocaleString()],
        ];

        lastResultText = rows.map(([label, value]) => `${label}: ${value}`).join('\n');
        resultContent.innerHTML = rows.map(([label, value]) => `
            <div class="d-flex justify-content-between gap-3 border-bottom pb-2">
                <span class="text-secondary">${label}</span>
                <span class="fw-semibold text-end">${value ?? 'N/A'}</span>
            </div>
        `).join('');
        resultWrap.classList.remove('d-none');
    }

    function renderNotFound(uniqueCode) {
        lastResultText = `Unique Code: ${uniqueCode}\nStatus: Invalid`;
        resultWrap.classList.remove('d-none');
        resultContent.innerHTML = '';
    }

    async function lookupBarcode(uniqueCode) {
        const code = (uniqueCode || '').trim();
        if (!code) {
            setStatus('Enter a barcode value first.', 'warning');
            return;
        }

        setStatus('Looking up barcode...', 'secondary');

        try {
            const response = await fetch(`/api/v1/scan/${encodeURIComponent(code)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json().catch(() => ({}));

            if (payload.data && payload.data.valid) {
                setStatus('Barcode found.', 'success');
                renderResult(payload.data);
                pushHistory(code, lastResultText, true);
                return;
            }

            renderNotFound(code);
            setStatus(payload.message || 'Invalid barcode. No product found.', 'danger');
            pushHistory(code, lastResultText, false);
        } catch (error) {
            renderNotFound(code);
            setStatus('Something went wrong while looking up the barcode.', 'danger');
            pushHistory(code, lastResultText, false);
        }
    }

    async function startPreview() {
        try {
            mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            video.srcObject = mediaStream;
            await video.play();
        } catch (error) {
            setStatus('Camera permission was denied or not available.', 'danger');
        }
    }

    function stopPreview() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        video.srcObject = null;
    }

    async function startScanner() {
        if (scannerRunning) {
            return;
        }

        if (!html5Qrcode) {
            html5Qrcode = new Html5Qrcode('scanner-reader');
        }

        try {
            scannerRunning = true;
            await html5Qrcode.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.CODE_39,
                    ],
                },
                async (decodedText) => {
                    await lookupBarcode(decodedText);
                }
            );
        } catch (error) {
            scannerRunning = false;
            setStatus('Unable to start the scanner.', 'danger');
        }
    }

    async function stopScanner() {
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
    }

    lookupBtn.addEventListener('click', () => lookupBarcode(manualCode.value));
    manualCode.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            lookupBarcode(manualCode.value);
        }
    });

    startCameraBtn.addEventListener('click', async () => {
        await startPreview();
        await startScanner();
    });

    stopCameraBtn.addEventListener('click', async () => {
        await stopScanner();
        setStatus('Camera stopped.', 'secondary');
    });

    scanFileBtn.addEventListener('click', async () => {
        const file = barcodeFile.files[0];
        if (!file) {
            setStatus('Choose an image file to scan.', 'warning');
            return;
        }

        if (!html5Qrcode) {
            html5Qrcode = new Html5Qrcode('scanner-reader');
        }

        try {
            const decodedText = await html5Qrcode.scanFile(file, true);
            await lookupBarcode(decodedText);
        } catch (error) {
            setStatus('No barcode could be read from that image.', 'danger');
        }
    });

    clearHistoryBtn.addEventListener('click', () => {
        localStorage.removeItem(historyKey);
        renderHistory();
        setHistoryMessage('History cleared.', 'secondary');
    });

    copyResultBtn.addEventListener('click', async () => {
        if (!lastResultText) {
            return;
        }
        await navigator.clipboard.writeText(lastResultText);
        setStatus('Result copied to clipboard.', 'success');
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
            setHistoryMessage('History item copied.', 'success');
            return;
        }

        if (button.dataset.action === 'delete') {
            items.splice(index, 1);
            setHistory(items);
            setHistoryMessage('History item deleted.', 'secondary');
        }
    });

    renderHistory();
</script>
@endpush

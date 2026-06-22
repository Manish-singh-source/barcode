@extends('layouts.app')

@section('content')
<div class="landing-shell">
    <div class="landing-glow landing-glow-left"></div>
    <div class="landing-glow landing-glow-right"></div>

    <nav class="navbar navbar-expand-lg landing-navbar rounded-4 px-3 px-md-4 mb-4 shadow-lg">
        <div class="container-fluid p-0">
            <a class="navbar-brand d-flex align-items-center gap-3 fw-semibold fs-4" href="{{ url('/') }}">
                <span class="brand-mark"><img src="{{ asset('logo.png') }}" alt="BarcodeMS logo" class="brand-logo-img" style="width:100%;height:100%;object-fit:contain;position:relative;z-index:1;border-radius:0.45rem;"></span>
                <span>Barcode</span>
            </a>
            <button class="navbar-toggler landing-toggler" type="button" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <div class="ms-auto d-grid gap-2 d-lg-flex pt-3 pt-lg-0">
                    <a href="{{ url('/login') }}" class="btn landing-btn-primary px-4">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-panel rounded-4 p-3 p-md-4 p-xl-5 mb-4 shadow-lg">
        <div class="row g-4 g-xl-5 align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title mb-3">Scan Any <span>Barcode</span><br>Instantly <span class="hero-lightning">⚡</span></h1>
                <p class="hero-copy mb-4">No login required. Point your camera, scan a code, and get product details in seconds with a clean, fast interface.</p>
                <div class="d-grid gap-2 d-sm-flex hero-actions">
                    <a href="#scanner-section" class="btn landing-btn-primary btn-lg px-4">Start Scanning</a>
                    <a href="{{ url('/login') }}" class="btn landing-btn-soft btn-lg px-4">Admin Login</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-feature-card">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <span class="text-uppercase small fw-semibold text-primary-emphasis hero-eyebrow">Live product access</span>
                        <span class="hero-status hero-status-fast">Fast</span>
                    </div>
                    <div class="row align-items-center g-4">
                        <div class="col-md-7">
                            <h2 class="hero-feature-title mb-3">Barcode + QR ready</h2>
                            <p class="hero-feature-copy mb-0">Designed for quick field scanning, manual lookups, and clear browser history tracking.</p>
                        </div>
                        <div class="col-md-5 text-center">
                            <div class="hero-illustration">
                                <div class="hero-illustration-core"></div>
                                <div class="hero-illustration-card"><span class="hero-illustration-bars"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="scanner-section" class="scanner-section mb-4">
        <div class="scanner-shell rounded-4 p-3 p-md-4 p-xl-5 shadow-lg">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon"><i class="bi bi-camera"></i></div>
                    <div>
                        <h2 class="section-title mb-1">Barcode Scanner</h2>
                        <p class="section-copy mb-0">Use the camera, enter a code manually, or upload an image to look up a product.</p>
                    </div>
                </div>
                <span class="hero-status hero-status-ready">Camera Ready</span>
            </div>

            <div class="scanner-frame rounded-4 p-3 p-md-4 mb-4">
                <div class="scanner-frame-inner rounded-4">
                    <video id="camera-preview" class="w-100 h-100 rounded-4 landing-camera-preview" autoplay muted playsinline></video>
                    <div class="scanner-overlay">
                        <div class="scanner-overlay-badge"><i class="bi bi-upc-scan"></i></div>
                        <div class="fw-semibold">Camera feed will appear here</div>
                        <div class="small text-white-50">Position the barcode within the frame</div>
                    </div>
                </div>
                <div id="scanner-reader" class="visually-hidden"></div>
            </div>

            <div class="d-grid gap-2 d-sm-flex mb-4 scanner-actions">
                <button type="button" id="start-camera-btn" class="btn landing-btn-primary btn-lg px-4">Start Camera</button>
                <button type="button" id="stop-camera-btn" class="btn landing-btn-danger-soft btn-lg px-4">Stop Camera</button>
            </div>
            <div class="lookup-grid">
                <div class="lookup-card">
                    <label for="manual-code" class="form-label fw-semibold mb-3">Or enter barcode manually</label>
                    <div class="input-group input-group-responsive lookup-input">
                        <input type="text" id="manual-code" class="form-control form-control-lg" placeholder="Enter unique code">
                        <button type="button" id="lookup-btn" class="btn landing-btn-primary">Lookup</button>
                    </div>
                </div>
                <div class="lookup-divider"><span>OR</span></div>
                <div class="lookup-card">
                    <label for="barcode-file" class="form-label fw-semibold mb-3">Or upload an image of the barcode</label>
                    <div class="input-group input-group-responsive lookup-input">
                        <input type="file" id="barcode-file" class="form-control form-control-lg" accept="image/*">
                        <button type="button" id="scan-file-btn" class="btn landing-btn-primary">Scan File</button>
                    </div>
                </div>
            </div>

            <div id="scanner-status" class="mt-4"></div>

            <div id="result-wrap" class="mt-4 d-none">
                <div class="result-card rounded-4 p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="result-kicker">Scan Result</div>
                            <h3 class="result-title mb-0">Product Details</h3>
                        </div>
                        <button type="button" id="copy-result-btn" class="btn btn-sm landing-btn-outline">Copy</button>
                    </div>
                    <div id="result-content" class="vstack gap-2"></div>
                </div>
            </div>

            <div id="history-alert" class="mt-4"></div>
        </div>
    </section>

    <section class="history-section mb-4">
        <div class="history-shell rounded-4 p-3 p-md-4 p-xl-5 shadow-lg">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon section-icon-history"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h2 class="section-title mb-1">Scan History</h2>
                        <p class="section-copy mb-0">Last 10 scans from this browser.</p>
                    </div>
                </div>
                <button type="button" id="clear-history-btn" class="btn btn-sm landing-btn-outline-danger">Clear All</button>
            </div>
            <div id="history-empty" class="history-empty rounded-4 d-none">No scan history yet.</div>
            <div id="history-list" class="history-list"></div>
        </div>
    </section>

    <footer class="landing-footer py-4 text-center">
        <small>&copy; {{ date('Y') }} Barcode. All rights reserved.</small>
    </footer>
</div>
@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
    body {
        font-family: 'Poppins', sans-serif;
        background:
            radial-gradient(circle at top left, rgba(114, 92, 255, 0.10), transparent 28%),
            radial-gradient(circle at top right, rgba(45, 212, 191, 0.10), transparent 22%),
            linear-gradient(180deg, #f7f8ff 0%, #eef2ff 52%, #f9fafb 100%);
    }

    .landing-shell {
        position: relative;
        overflow: hidden;
    }

    .landing-glow {
        position: absolute;
        width: 28rem;
        height: 28rem;
        border-radius: 999px;
        filter: blur(64px);
        opacity: 0.25;
        pointer-events: none;
        z-index: 0;
    }

    .landing-glow-left {
        top: -8rem;
        left: -10rem;
        background: rgba(90, 72, 255, 0.35);
    }

    .landing-glow-right {
        right: -12rem;
        top: 12rem;
        background: rgba(67, 211, 255, 0.28);
    }

    .landing-navbar,
    .hero-panel,
    .scanner-shell,
    .history-shell,
    .landing-footer {
        position: relative;
        z-index: 1;
    }

    .landing-navbar {
        background: linear-gradient(135deg, rgba(11, 16, 49, 0.96), rgba(19, 24, 71, 0.96));
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .navbar-brand {
        color: #f8faff;
        letter-spacing: -0.02em;
    }

    .navbar-brand:hover {
        color: #f8faff;
    }
    .brand-mark {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed, #2563eb);
        box-shadow: 0 14px 30px rgba(79, 70, 229, 0.35);
        position: relative;
    }

    .landing-toggler.navbar-toggler { border-color: rgba(255, 255, 255, 0.16); }
    .landing-toggler .navbar-toggler-icon { filter: invert(1) brightness(2); }

    .landing-btn-primary {
        color: #fff;
        border: 0;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        box-shadow: 0 14px 30px rgba(79, 70, 229, 0.25);
    }

    .landing-btn-primary:hover,
    .landing-btn-primary:focus {
        color: #fff;
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    }

    .landing-btn-outline {
        color: #eef2ff;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
    }

    .landing-btn-outline:hover,
    .landing-btn-outline:focus {
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.28);
    }

    .landing-btn-soft {
        color: #1d4ed8;
        border: 1px solid rgba(59, 130, 246, 0.16);
        background: rgba(255, 255, 255, 0.82);
    }

    .landing-btn-soft:hover,
    .landing-btn-soft:focus { color: #1d4ed8; background: #fff; }

    .landing-btn-danger-soft {
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.20);
        background: rgba(255, 255, 255, 0.88);
    }

    .landing-btn-danger-soft:hover,
    .landing-btn-danger-soft:focus { color: #dc2626; background: #fff; }

    .landing-btn-outline-danger {
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.18);
        background: rgba(255, 255, 255, 0.72);
    }

    .hero-panel,
    .scanner-shell,
    .history-shell {
        background: rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 26px 60px rgba(15, 23, 42, 0.08);
    }

    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        color: #4f46e5;
        font-weight: 700;
        font-size: 0.95rem;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.14), rgba(59, 130, 246, 0.10));
        border: 1px solid rgba(99, 102, 241, 0.14);
    }

    .hero-pill-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(79, 70, 229, 0.1);
    }

    .hero-title {
        color: #1f2452;
        font-size: clamp(2.6rem, 5vw, 4.6rem);
        line-height: 0.96;
        font-weight: 800;
        letter-spacing: -0.05em;
    }

    .hero-title span { color: #4f46e5; }
    .hero-lightning { color: #b794f4 !important; }
    .hero-copy,
    .section-copy,
    .hero-feature-copy { color: #5b6478; font-size: 1.05rem; line-height: 1.75; }

    .hero-feature-card {
        position: relative;
        min-height: 100%;
        padding: 2rem;
        border-radius: 2rem;
        border: 1px solid rgba(99, 102, 241, 0.16);
        background:
            radial-gradient(circle at top right, rgba(191, 219, 254, 0.85), transparent 38%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(234, 242, 255, 0.94));
        overflow: hidden;
    }

    .hero-feature-card::after {
        content: '';
        position: absolute;
        inset: auto -25% -30% auto;
        width: 14rem;
        height: 14rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.18), transparent 60%);
        pointer-events: none;
    }

    .hero-feature-title {
        color: #222752;
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        line-height: 1.08;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .hero-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
    }

    .hero-status-fast, .hero-status-ready { color: #059669; }
    .hero-status-fast::before,
    .hero-status-ready::before {
        content: '';
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 0 5px rgba(5, 150, 105, 0.12);
    }

    .hero-status-ready { background: rgba(236, 253, 245, 0.95); }

    .hero-illustration {
        position: relative;
        min-height: 13rem;
        display: grid;
        place-items: center;
    }

    .hero-illustration-core {
        position: absolute;
        width: 10rem;
        height: 10rem;
        border-radius: 2.5rem;
        transform: rotate(15deg);
        background: linear-gradient(145deg, rgba(147, 197, 253, 0.55), rgba(167, 139, 250, 0.40));
        box-shadow: 0 22px 45px rgba(79, 70, 229, 0.18);
    }

    .hero-illustration-card {
        position: relative;
        width: 7rem;
        height: 10rem;
        border-radius: 1.8rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(239, 246, 255, 0.85));
        border: 1px solid rgba(255, 255, 255, 0.88);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 18px 30px rgba(79, 70, 229, 0.14);
    }

    .hero-illustration-bars {
        width: 2.7rem;
        height: 5.8rem;
        border-radius: 0.9rem;
        display: block;
        background: linear-gradient(90deg, transparent 0 8%, #8b5cf6 8% 18%, transparent 18% 28%, #8b5cf6 28% 38%, transparent 38% 48%, #8b5cf6 48% 58%, transparent 58% 68%, #8b5cf6 68% 78%, transparent 78% 100%);
        box-shadow: 0 0 0 10px rgba(139, 92, 246, 0.08);
    }

    .section-icon {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #4f46e5;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.18), rgba(168, 85, 247, 0.12));
    }

    .section-icon-history { color: #7c3aed; }
    .section-title { color: #1f2452; font-size: 1.65rem; font-weight: 800; letter-spacing: -0.04em; }

    .scanner-frame { background: linear-gradient(135deg, rgba(18, 18, 45, 0.96), rgba(40, 31, 88, 0.95)); border: 1px solid rgba(129, 140, 248, 0.30); padding: 0.5rem !important; }
    .scanner-frame-inner {
        position: relative;
        height: 300px;
        min-height: 300px;
        overflow: hidden;
        background: radial-gradient(circle at center, rgba(99, 102, 241, 0.22), transparent 40%), linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(30, 27, 75, 0.95));
        border: 1px solid rgba(129, 140, 248, 0.16);
    }

    .scanner-frame-inner::before,
    .scanner-frame-inner::after {
        content: '';
        position: absolute;
        width: 1.8rem;
        height: 1.8rem;
        border-color: rgba(129, 140, 248, 0.95);
        border-style: solid;
        z-index: 2;
    }

    .scanner-frame-inner::before { top: 1rem; left: 1rem; border-width: 2px 0 0 2px; border-top-left-radius: 0.7rem; }
    .scanner-frame-inner::after { right: 1rem; bottom: 1rem; border-width: 0 2px 2px 0; border-bottom-right-radius: 0.7rem; }

    .landing-camera-preview { height: 300px; min-height: 300px; object-fit: cover; background: transparent; }

    .scanner-overlay {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        gap: 0.55rem;
        color: #fff;
        text-align: center;
        pointer-events: none;
        z-index: 1;
        background: linear-gradient(180deg, rgba(17, 24, 39, 0.02), rgba(17, 24, 39, 0.12));
    }

    .scanner-overlay-badge {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
        background: rgba(99, 102, 241, 0.22);
        box-shadow: 0 0 0 12px rgba(99, 102, 241, 0.08);
    }

    .lookup-grid { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); gap: 1rem; align-items: stretch; }
    .lookup-card { padding: 1.25rem; border-radius: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.16); background: rgba(255, 255, 255, 0.72); }
    .lookup-divider { display: grid; place-items: center; color: #64748b; font-weight: 800; letter-spacing: 0.08em; }
    .lookup-divider span { width: 2.6rem; height: 2.6rem; border-radius: 999px; display: grid; place-items: center; background: rgba(255, 255, 255, 0.92); border: 1px solid rgba(148, 163, 184, 0.18); }
    .lookup-input .form-control { border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.92); }
    .lookup-input .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.12); }

    .result-card { background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(241, 245, 255, 0.84)); border: 1px solid rgba(99, 102, 241, 0.12); }
    .result-kicker { color: #6366f1; font-size: 0.85rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
    .result-title { color: #1f2452; font-weight: 800; letter-spacing: -0.03em; }
    #result-content > div:last-child { border-bottom: 0 !important; padding-bottom: 0 !important; }
    #result-content span:last-child { color: #111827; }

    .history-empty { padding: 2.25rem 1.5rem; text-align: center; color: #64748b; background: rgba(255, 255, 255, 0.74); border: 1px solid rgba(148, 163, 184, 0.14); }
    .history-list { display: grid; gap: 0.85rem; }
    #history-list .list-group-item { border: 0; padding: 0; background: transparent; }
    #history-list .history-row,
    .history-item { padding: 1rem 1.1rem; border-radius: 1.1rem; background: rgba(255, 255, 255, 0.86); border: 1px solid rgba(148, 163, 184, 0.16); box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05); }
    .landing-footer { color: #6b7280; }

    @media (max-width: 991.98px) {
        .lookup-grid { grid-template-columns: 1fr; }
        .lookup-divider { min-height: 2rem; }
        .lookup-divider span { width: 2.2rem; height: 2.2rem; }
    }

    @media (max-width: 575.98px) {
        .landing-shell { padding: 0.85rem; }
        .hero-actions .btn,
        .scanner-actions .btn,
        .input-group-responsive > .btn,
        .input-group-responsive > .form-control,
        .navbar .btn { width: 100%; }
        .input-group-responsive { flex-direction: column; }
        .input-group-responsive > .form-control,
        .input-group-responsive > .btn { border-radius: 0.9rem !important; }
        .scanner-frame-inner,
        .landing-camera-preview { height: 300px; min-height: 300px; }
        .scanner-overlay { padding: 1rem; }
        .hero-title { font-size: 2.4rem; }
    }
</style>
@endpush

@push('scripts')

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const navbarToggler = document.querySelector('.navbar-toggler');
    const mainNavbar = document.getElementById('mainNavbar');
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

    if (navbarToggler && mainNavbar) {
        navbarToggler.addEventListener('click', function () {
            const isOpen = mainNavbar.classList.toggle('show');
            navbarToggler.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
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


















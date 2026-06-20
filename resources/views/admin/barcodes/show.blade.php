@extends('layouts.admin')

@section('content')
@php
    $snapshot = $barcode->resolvedProductSnapshot();
    $barcodeImageUrl = $barcode->barcode_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($barcode->barcode_image_path) : null;
    $formatValue = $barcode->barcode_format?->value ?? $barcode->barcode_format;
    $formatClass = [
        'code128' => 'primary',
        'qrcode' => 'success',
        'code39' => 'warning',
        'ean13' => 'info',
    ][$formatValue] ?? 'secondary';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <a href="{{ url('/barcodes') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
    <div class="d-flex gap-2">
        <button type="button" id="editBtn" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </button>
        <button type="button" id="deleteBtn" class="btn btn-danger">
            <i class="bi bi-trash me-1"></i>Delete
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-xl-5 text-center">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h2 class="h4 fw-bold mb-1">Barcode Details</h2>
                        <p class="text-secondary mb-0">Generated barcode preview and download options.</p>
                    </div>
                    <span class="badge text-bg-{{ $barcode->is_active ? 'success' : 'secondary' }}">{{ $barcode->is_active ? 'Active' : 'Inactive' }}</span>
                </div>

                <div class="text-center mb-4">
                    @if ($barcodeImageUrl)
                        <img src="{{ $barcodeImageUrl }}" alt="Barcode preview" class="img-fluid rounded-4 border bg-white p-3 shadow-sm" style="max-width: 400px;">
                    @else
                        <div class="border rounded-4 bg-light p-5 text-secondary">No preview available</div>
                    @endif
                </div>

                <div class="fw-semibold fs-5 mb-4 font-monospace">{{ $barcode->custom_label ?: $barcode->unique_code }}</div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    @if ($barcodeImageUrl)
                        <button type="button" id="downloadPngBtn" class="btn btn-dark">Download PNG</button>
                    @endif
                    <button type="button" id="downloadSvgBtn" class="btn btn-outline-dark">Download SVG</button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h5 fw-bold mb-3">Barcode Data</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-secondary small mb-1">Unique Code</div>
                        <div class="badge text-bg-dark font-monospace fs-6 px-3 py-2">{{ $barcode->unique_code }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small mb-1">Format</div>
                        <div><span class="badge text-bg-{{ $formatClass }}">{{ $formatValue }}</span></div>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary small mb-1">Raw Barcode Content</div>
                        <pre class="p-3 bg-light rounded-3 mb-0 text-break" style="white-space: pre-wrap; word-break: break-word;">{{ $barcode->barcode_data }}</pre>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small mb-1">Created At</div>
                        <div>{{ optional($barcode->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small mb-1">Updated At</div>
                        <div>{{ optional($barcode->updated_at)->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h5 fw-bold mb-3">Barcode Snapshot</h3>
                <div class="fw-bold fs-5 mb-2">{{ $snapshot['name'] ?? $barcode->barcode_data }}</div>
                <div class="text-secondary">{{ $snapshot['description'] ?: 'No structured description available.' }}</div>
                <div class="mt-3 small text-secondary">
                    @if (! empty($snapshot['sku'])) SKU: {{ $snapshot['sku'] }} @endif
                    @if (! empty($snapshot['brand'])) {{ ! empty($snapshot['sku']) ? ' | ' : '' }}Brand: {{ $snapshot['brand'] }} @endif
                    @if (! empty($snapshot['category'])) {{ (! empty($snapshot['sku']) || ! empty($snapshot['brand'])) ? ' | ' : '' }}Category: {{ $snapshot['category'] }} @endif
                    @if (! empty($snapshot['unit'])) {{ (! empty($snapshot['sku']) || ! empty($snapshot['brand']) || ! empty($snapshot['category'])) ? ' | ' : '' }}Unit: {{ $snapshot['unit'] }} @endif
                    @if (! is_null($snapshot['stock_quantity'])) {{ (! empty($snapshot['sku']) || ! empty($snapshot['brand']) || ! empty($snapshot['category']) || ! empty($snapshot['unit'])) ? ' | ' : '' }}Stock: {{ $snapshot['stock_quantity'] }} @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h5 fw-bold mb-3">Scan Statistics</h3>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-secondary">Total Scans</div>
                    <div class="badge text-bg-primary fs-6 px-3 py-2">{{ $barcode->scan_logs_count ?? 0 }}</div>
                </div>
                <div class="text-secondary small mb-1">Last Scanned At</div>
                <div>{{ $barcode->scan_logs_max_created_at ? \Illuminate\Support\Carbon::parse($barcode->scan_logs_max_created_at)->format('Y-m-d H:i') : 'Never scanned' }}</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h5 fw-bold mb-3">Generated By</h3>
                <div class="fw-semibold">{{ $barcode->user?->name ?? '—' }}</div>
                <div class="text-secondary">{{ $barcode->user?->email ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form id="editBarcodeForm" method="POST" action="{{ url('/barcodes/' . $barcode->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body vstack gap-3">
                    <div>
                        <label for="editCustomLabel" class="form-label fw-semibold">Custom Label</label>
                        <input type="text" name="custom_label" id="editCustomLabel" class="form-control" value="{{ $barcode->custom_label }}" placeholder="Update label">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form id="deleteBarcodeForm" method="POST" action="{{ url('/barcodes/' . $barcode->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this barcode? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .font-monospace {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace !important;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const editModal = new bootstrap.Modal(document.getElementById('editBarcodeModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteBarcodeModal'));
        const editBtn = document.getElementById('editBtn');
        const deleteBtn = document.getElementById('deleteBtn');
        const editCustomLabel = document.getElementById('editCustomLabel');
        const editBarcodeForm = document.getElementById('editBarcodeForm');
        const deleteBarcodeForm = document.getElementById('deleteBarcodeForm');
        const downloadPngBtn = document.getElementById('downloadPngBtn');
        const downloadSvgBtn = document.getElementById('downloadSvgBtn');

        const barcodeId = {{ (int) $barcode->id }};
        const barcodeImageUrl = @json($barcodeImageUrl);
        const uniqueCode = @json($barcode->unique_code);
        const barcodeSvg = @json($barcode->barcode_svg ?? '');

        editBtn.addEventListener('click', function () {
            editBarcodeForm.action = '{{ url('/barcodes') }}/' + barcodeId;
            editCustomLabel.value = @json($barcode->custom_label ?? '');
            editModal.show();
        });

        deleteBtn.addEventListener('click', function () {
            deleteBarcodeForm.action = '{{ url('/barcodes') }}/' + barcodeId;
            deleteModal.show();
        });

        function downloadBlob(url, filename) {
            fetch(url)
                .then((response) => response.blob())
                .then((blob) => {
                    const objectUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = objectUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
                });
        }

        if (downloadPngBtn) {
            downloadPngBtn.addEventListener('click', function () {
                if (barcodeImageUrl) {
                    downloadBlob(barcodeImageUrl, uniqueCode + '.png');
                }
            });
        }

        if (downloadSvgBtn) {
            downloadSvgBtn.addEventListener('click', function () {
                if (!barcodeSvg) {
                    return;
                }

                const blob = new Blob([barcodeSvg], { type: 'image/svg+xml;charset=utf-8' });
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = uniqueCode + '.svg';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
            });
        }
    })();
</script>
@endpush

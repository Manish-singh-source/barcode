@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Generated Barcodes</h4>
        <p class="text-secondary mb-0">Barcode records from the `barcode_generations` table.</p>
    </div>
    <a href="{{ url('/barcodes/generate') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Generate New Barcode
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        @if ($barcodes->isEmpty())
            <div class="alert alert-light border mb-0">No barcode records found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Unique Code</th>
                            <th>Format</th>
                            <th>Custom Label</th>
                            <th>Barcode Data</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($barcodes as $barcode)
                            @php($format = $barcode->barcode_format?->value ?? $barcode->barcode_format)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td class="font-monospace fw-semibold">{{ $barcode->unique_code }}</td>
                                <td>
                                    <span class="badge text-bg-{{ ['code128' => 'primary', 'qrcode' => 'success', 'code39' => 'warning', 'ean13' => 'info'][$format] ?? 'secondary' }}">{{ $format }}</span>
                                </td>
                                <td>{{ \App\Models\BarcodeGeneration::normalizeText($barcode->custom_label) ?: 'N/A' }}</td>
                                <td style="max-width: 320px; white-space: pre-wrap; word-break: break-word;">{{ \App\Models\BarcodeGeneration::normalizeText($barcode->barcode_data) }}</td>
                                <td>{{ optional($barcode->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ url('/barcodes/' . $barcode->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-action="edit"
                                            data-id="{{ $barcode->id }}"
                                            data-barcode-data="{{ e(\App\Models\BarcodeGeneration::normalizeText($barcode->barcode_data ?? '')) }}"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-action="delete"
                                            data-id="{{ $barcode->id }}"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="editBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form id="editBarcodeForm" method="POST" action="{{ url('/barcodes/0') }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Barcode Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body vstack gap-3">
                    <div>
                        <label for="editBarcodeData" class="form-label fw-semibold">Barcode Data</label>
                        <textarea name="barcode_data" id="editBarcodeData" class="form-control" rows="4" placeholder="Update barcode data"></textarea>
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
            <form id="deleteBarcodeForm" method="POST" action="{{ url('/barcodes/0') }}">
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

    .modal.is-open {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const editModalEl = document.getElementById('editBarcodeModal');
        const deleteModalEl = document.getElementById('deleteBarcodeModal');
        const editBarcodeForm = document.getElementById('editBarcodeForm');
        const deleteBarcodeForm = document.getElementById('deleteBarcodeForm');
        const editBarcodeData = document.getElementById('editBarcodeData');
        const body = document.body;
        let activeBackdrop = null;

        function openModal(modalEl) {
            if (!modalEl) {
                return;
            }

            modalEl.classList.add('show', 'is-open');
            modalEl.setAttribute('aria-modal', 'true');
            modalEl.removeAttribute('aria-hidden');

            if (!activeBackdrop) {
                activeBackdrop = document.createElement('div');
                activeBackdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(activeBackdrop);
            }

            body.classList.add('modal-open');
            body.style.overflow = 'hidden';
        }

        function closeModal(modalEl) {
            if (!modalEl) {
                return;
            }

            modalEl.classList.remove('show', 'is-open');
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');

            if (activeBackdrop) {
                activeBackdrop.remove();
                activeBackdrop = null;
            }

            body.classList.remove('modal-open');
            body.style.overflow = '';
        }

        function wireDismissButtons(modalEl) {
            modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeModal(modalEl);
                });
            });
        }

        wireDismissButtons(editModalEl);
        wireDismissButtons(deleteModalEl);

        document.querySelectorAll('[data-action="edit"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = button.getAttribute('data-id');
                const barcodeData = button.getAttribute('data-barcode-data') || '';

                if (id) {
                    editBarcodeForm.action = '{{ url('/barcodes') }}/' + id;
                }

                editBarcodeData.value = barcodeData;
                openModal(editModalEl);
            });
        });

        document.querySelectorAll('[data-action="delete"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = button.getAttribute('data-id');

                if (id) {
                    deleteBarcodeForm.action = '{{ url('/barcodes') }}/' + id;
                }

                openModal(deleteModalEl);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(editModalEl);
                closeModal(deleteModalEl);
            }
        });

        [editModalEl, deleteModalEl].forEach(function (modalEl) {
            modalEl.addEventListener('click', function (event) {
                if (event.target === modalEl) {
                    closeModal(modalEl);
                }
            });
        });
    })();
</script>
@endpush

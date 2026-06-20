@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Generated Barcodes</h4>
        <p class="text-secondary mb-0">Review, edit, and remove generated barcode records.</p>
    </div>
    <a href="{{ url('/barcodes/generate') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Generate New Barcode
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 position-relative">
    <div id="tableLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-white bg-opacity-75 rounded-4" style="z-index: 5;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            <div class="small text-secondary mt-2">Loading barcodes...</div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="barcodesTable" class="table table-striped table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Unique Code</th>
                        <th>Format</th>
                        <th>Custom Label</th>
                        <th>Linked Product</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Update Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body vstack gap-3">
                <input type="hidden" id="editBarcodeId">
                <div>
                    <label for="editCustomLabel" class="form-label fw-semibold">Custom Label</label>
                    <input type="text" id="editCustomLabel" class="form-control" placeholder="Update label">
                </div>
                <div>
                    <label for="editProductId" class="form-label fw-semibold">Link to Product</label>
                    <select id="editProductId" class="form-select">
                        <option value="">No product selected</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveEditBtn" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Delete Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteBarcodeId">
                <p class="mb-0">Are you sure you want to delete this barcode? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .barcode-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .dt-action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    table.dataTable tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    (function () {
        const tableLoadingOverlay = document.getElementById('tableLoadingOverlay');
        const editModalEl = document.getElementById('editBarcodeModal');
        const deleteModalEl = document.getElementById('deleteBarcodeModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const editBarcodeId = document.getElementById('editBarcodeId');
        const editCustomLabel = document.getElementById('editCustomLabel');
        const editProductId = document.getElementById('editProductId');
        const deleteBarcodeId = document.getElementById('deleteBarcodeId');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        let barcodeRows = [];
        let productsCache = [];
        let table = null;

        function setLoading(isLoading) {
            tableLoadingOverlay.classList.toggle('d-none', !isLoading);
            tableLoadingOverlay.classList.toggle('d-flex', isLoading);
        }

        function formatDate(value) {
            if (!value) {
                return '—';
            }

            return new Date(value).toLocaleString();
        }

        function formatBadge(format) {
            const map = {
                code128: 'primary',
                qrcode: 'success',
                code39: 'warning',
                ean13: 'info',
            };

            const cls = map[format] || 'secondary';
            return '<span class="badge text-bg-' + cls + '">' + format + '</span>';
        }

        function actionButtons(id) {
            return [
                '<a href="{{ url('/barcodes') }}/' + id + '" class="btn btn-sm btn-outline-secondary dt-action-btn" title="View">',
                '<i class="bi bi-eye"></i>',
                '</a>',
                '<button type="button" class="btn btn-sm btn-outline-primary dt-action-btn ms-1 js-edit-btn" data-id="' + id + '" title="Edit">',
                '<i class="bi bi-pencil"></i>',
                '</button>',
                '<button type="button" class="btn btn-sm btn-outline-danger dt-action-btn ms-1 js-delete-btn" data-id="' + id + '" title="Delete">',
                '<i class="bi bi-trash"></i>',
                '</button>',
            ].join('');
        }

        function getProductOptions() {
            return '<option value="">No product selected</option>' + productsCache.map(function (product) {
                return '<option value="' + product.id + '">' + product.name + (product.sku ? ' (' + product.sku + ')' : '') + '</option>';
            }).join('');
        }

        async function loadProducts() {
            try {
                const response = await fetch('/api/v1/products?per_page=100', {
                    headers: setAuthHeaders(),
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(getApiErrorMessage(payload));
                }

                productsCache = payload.data?.data || [];
                editProductId.innerHTML = getProductOptions();
            } catch (error) {
                productsCache = [];
                editProductId.innerHTML = '<option value="">Unable to load products</option>';
            }
        }

        function findRow(id) {
            return barcodeRows.find(function (row) {
                return String(row.id) === String(id);
            }) || null;
        }

        async function saveBarcode() {
            const id = editBarcodeId.value;
            if (!id) {
                return;
            }

            saveEditBtn.disabled = true;

            try {
                const response = await fetch('/api/v1/barcodes/' + id, {
                    method: 'PATCH',
                    headers: setAuthHeaders(),
                    body: JSON.stringify({
                        custom_label: editCustomLabel.value.trim() || null,
                        product_id: editProductId.value || null,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(getApiErrorMessage(payload, 'Unable to update barcode.'));
                    return;
                }

                editModal.hide();
                table.ajax.reload(null, false);
            } catch (error) {
                alert('Unable to update barcode right now.');
            } finally {
                saveEditBtn.disabled = false;
            }
        }

        async function deleteBarcode() {
            const id = deleteBarcodeId.value;
            if (!id) {
                return;
            }

            confirmDeleteBtn.disabled = true;

            try {
                const response = await fetch('/api/v1/barcodes/' + id, {
                    method: 'DELETE',
                    headers: setAuthHeaders(),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(getApiErrorMessage(payload, 'Unable to delete barcode.'));
                    return;
                }

                deleteModal.hide();
                table.ajax.reload(null, false);
            } catch (error) {
                alert('Unable to delete barcode right now.');
            } finally {
                confirmDeleteBtn.disabled = false;
            }
        }

        table = $('#barcodesTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: '/api/v1/barcodes',
                type: 'GET',
                headers: setAuthHeaders(),
                dataSrc: function (json) {
                    barcodeRows = json.data || [];
                    return json.data || [];
                },
                beforeSend: function (xhr) {
                    const headers = setAuthHeaders();
                    Object.keys(headers).forEach(function (key) {
                        xhr.setRequestHeader(key, headers[key]);
                    });
                    setLoading(true);
                },
                complete: function () {
                    setLoading(false);
                },
                error: function () {
                    setLoading(false);
                }
            },
            columns: [
                { data: 'row_number', orderable: false, searchable: false },
                {
                    data: 'unique_code',
                    render: function (data) {
                        return '<span class="barcode-code">' + data + '</span>';
                    }
                },
                {
                    data: 'barcode_format',
                    render: function (data) {
                        return formatBadge(data);
                    }
                },
                { data: 'custom_label', render: function (data) { return data || '—'; } },
                { data: 'product_name', render: function (data) { return data || '—'; } },
                {
                    data: 'created_at',
                    render: function (data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return actionButtons(data);
                    }
                }
            ]
        });

        $('#barcodesTable').on('click', '.js-edit-btn', function () {
            const id = this.dataset.id;
            const row = findRow(id);
            if (!row) {
                return;
            }

            editBarcodeId.value = row.id;
            editCustomLabel.value = row.custom_label || '';
            editProductId.innerHTML = getProductOptions();
            editProductId.value = row.product_id || '';
            editModal.show();
        });

        $('#barcodesTable').on('click', '.js-delete-btn', function () {
            const id = this.dataset.id;
            deleteBarcodeId.value = id;
            deleteModal.show();
        });

        saveEditBtn.addEventListener('click', saveBarcode);
        confirmDeleteBtn.addEventListener('click', deleteBarcode);

        loadProducts();
    })();
</script>
@endpush
@extends('layouts.admin')

@section('content')
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

<div id="detailLoading" class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-5 text-center">
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        <div class="text-secondary mt-3">Loading barcode details...</div>
    </div>
</div>

<div id="detailContent" class="d-none">
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-xl-5 text-center">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Barcode Details</h2>
                            <p class="text-secondary mb-0">Generated barcode preview and download options.</p>
                        </div>
                        <span id="activeBadge" class="badge text-bg-secondary">Inactive</span>
                    </div>

                    <div class="text-center mb-4">
                        <img id="barcodeImage" alt="Barcode preview" class="img-fluid rounded-4 border bg-white p-3 shadow-sm" style="max-width: 400px; display: none;">
                        <div id="barcodeImagePlaceholder" class="border rounded-4 bg-light p-5 text-secondary">No preview available</div>
                    </div>

                    <div id="humanReadableText" class="fw-semibold fs-5 mb-4 font-monospace"></div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" id="downloadPngBtn" class="btn btn-dark">Download PNG</button>
                        <button type="button" id="downloadSvgBtn" class="btn btn-outline-dark">Download SVG</button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-bold mb-3">Encoded Information</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small mb-1">Unique Code</div>
                            <div id="uniqueCodeDisplay" class="badge text-bg-dark font-monospace fs-6 px-3 py-2"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small mb-1">Format</div>
                            <div id="formatBadge"></div>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small mb-1">Barcode Data</div>
                            <pre id="barcodeDataDisplay" class="p-3 bg-light rounded-3 mb-0 text-break"></pre>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small mb-1">Created At</div>
                            <div id="createdAtDisplay"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small mb-1">Updated At</div>
                            <div id="updatedAtDisplay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-bold mb-3">Linked Product</h3>
                    <div id="productEmpty" class="text-secondary">No product linked.</div>
                    <div id="productContent" class="d-none">
                        <div class="fw-bold fs-5 mb-2" id="productName"></div>
                        <div class="mb-1"><span class="text-secondary">SKU:</span> <span id="productSku"></span></div>
                        <div class="mb-1"><span class="text-secondary">Price:</span> <span id="productPrice"></span></div>
                        <div class="mb-1"><span class="text-secondary">Brand:</span> <span id="productBrand"></span></div>
                        <div class="mb-1"><span class="text-secondary">Category:</span> <span id="productCategory"></span></div>
                        <div class="text-secondary mt-3" id="productDescription"></div>
                        <a href="#" class="btn btn-outline-primary btn-sm mt-3 disabled">View Product</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-bold mb-3">Scan Statistics</h3>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="text-secondary">Total Scans</div>
                        <div id="scanCountBadge" class="badge text-bg-primary fs-6 px-3 py-2">0</div>
                    </div>
                    <div class="text-secondary small mb-1">Last Scanned At</div>
                    <div id="lastScannedAt">Never scanned</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-bold mb-3">Generated By</h3>
                    <div class="fw-semibold" id="userName"></div>
                    <div class="text-secondary" id="userEmail"></div>
                </div>
            </div>
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
                <div id="editError" class="alert alert-danger d-none mb-0" role="alert"></div>
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
<style>
    .font-monospace {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace !important;
    }

    #barcodeDataDisplay {
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const detailLoading = document.getElementById('detailLoading');
        const detailContent = document.getElementById('detailContent');
        const editModal = new bootstrap.Modal(document.getElementById('editBarcodeModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteBarcodeModal'));
        const editBtn = document.getElementById('editBtn');
        const deleteBtn = document.getElementById('deleteBtn');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const editBarcodeId = document.getElementById('editBarcodeId');
        const editCustomLabel = document.getElementById('editCustomLabel');
        const editProductId = document.getElementById('editProductId');
        const editError = document.getElementById('editError');
        const barcodeImage = document.getElementById('barcodeImage');
        const barcodeImagePlaceholder = document.getElementById('barcodeImagePlaceholder');
        const humanReadableText = document.getElementById('humanReadableText');
        const uniqueCodeDisplay = document.getElementById('uniqueCodeDisplay');
        const formatBadgeEl = document.getElementById('formatBadge');
        const barcodeDataDisplay = document.getElementById('barcodeDataDisplay');
        const activeBadge = document.getElementById('activeBadge');
        const createdAtDisplay = document.getElementById('createdAtDisplay');
        const updatedAtDisplay = document.getElementById('updatedAtDisplay');
        const productEmpty = document.getElementById('productEmpty');
        const productContent = document.getElementById('productContent');
        const productName = document.getElementById('productName');
        const productSku = document.getElementById('productSku');
        const productPrice = document.getElementById('productPrice');
        const productBrand = document.getElementById('productBrand');
        const productCategory = document.getElementById('productCategory');
        const productDescription = document.getElementById('productDescription');
        const scanCountBadge = document.getElementById('scanCountBadge');
        const lastScannedAt = document.getElementById('lastScannedAt');
        const userName = document.getElementById('userName');
        const userEmail = document.getElementById('userEmail');
        const downloadPngBtn = document.getElementById('downloadPngBtn');
        const downloadSvgBtn = document.getElementById('downloadSvgBtn');

        let currentData = null;
        let productsCache = [];
        let currentId = window.location.pathname.replace(/\/+$/, '').split('/').pop();

        function authHeaders() {
            return {
                Authorization: 'Bearer ' + localStorage.getItem('auth_token'),
                Accept: 'application/json',
                'Content-Type': 'application/json',
            };
        }

        function formatDate(value) {
            if (!value) {
                return '—';
            }

            return new Date(value).toLocaleString();
        }

        function formatBadgeMarkup(format) {
            const map = {
                code128: 'primary',
                qrcode: 'success',
                code39: 'warning',
                ean13: 'info',
            };

            const cls = map[format] || 'secondary';
            return '<span class="badge text-bg-' + cls + '">' + format + '</span>';
        }

        function showEditError(message) {
            if (!message) {
                editError.classList.add('d-none');
                editError.textContent = '';
                return;
            }

            editError.textContent = message;
            editError.classList.remove('d-none');
        }

        function showContent() {
            detailLoading.classList.add('d-none');
            detailContent.classList.remove('d-none');
        }

        function showLoading() {
            detailLoading.classList.remove('d-none');
            detailContent.classList.add('d-none');
        }

        function populateProductOptions() {
            editProductId.innerHTML = '<option value="">No product selected</option>' + productsCache.map(function (product) {
                return '<option value="' + product.id + '">' + product.name + (product.sku ? ' (' + product.sku + ')' : '') + '</option>';
            }).join('');
        }

        async function loadProducts() {
            const response = await fetch('/api/v1/products?per_page=100', {
                headers: authHeaders(),
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(getApiErrorMessage(payload, 'Unable to load products.'));
            }

            productsCache = payload.data?.data || [];
            populateProductOptions();
        }

        async function loadDetails() {
            showLoading();

            const response = await fetch('/api/v1/barcodes/' + currentId, {
                headers: authHeaders(),
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(getApiErrorMessage(payload, 'Unable to load barcode.'));
            }

            currentData = payload.data;
            const data = currentData;

            if (data.barcode_image_url) {
                barcodeImage.src = data.barcode_image_url;
                barcodeImage.style.display = 'inline-block';
                barcodeImagePlaceholder.classList.add('d-none');
            } else {
                barcodeImage.style.display = 'none';
                barcodeImagePlaceholder.classList.remove('d-none');
            }

            humanReadableText.textContent = data.custom_label || data.unique_code || '';
            uniqueCodeDisplay.textContent = data.unique_code || '';
            formatBadgeEl.innerHTML = formatBadgeMarkup(data.barcode_format);
            barcodeDataDisplay.textContent = data.barcode_data || '';
            activeBadge.className = 'badge ' + (data.is_active ? 'text-bg-success' : 'text-bg-secondary');
            activeBadge.textContent = data.is_active ? 'Active' : 'Inactive';
            createdAtDisplay.textContent = formatDate(data.created_at);
            updatedAtDisplay.textContent = formatDate(data.updated_at);
            scanCountBadge.textContent = String(data.scan_count || 0);
            lastScannedAt.textContent = data.last_scanned_at ? formatDate(data.last_scanned_at) : 'Never scanned';
            userName.textContent = data.user ? data.user.name : '—';
            userEmail.textContent = data.user ? data.user.email : '—';

            if (data.product) {
                productEmpty.classList.add('d-none');
                productContent.classList.remove('d-none');
                productName.textContent = data.product.name || '—';
                productSku.textContent = data.product.sku || '—';
                productPrice.textContent = data.product.price ?? '—';
                productBrand.textContent = data.product.brand || '—';
                productCategory.textContent = data.product.category || '—';
                productDescription.textContent = data.product.description || '—';
            } else {
                productContent.classList.add('d-none');
                productEmpty.classList.remove('d-none');
            }

            showContent();
        }

        async function saveBarcode() {
            if (!currentData) {
                return;
            }

            saveEditBtn.disabled = true;
            showEditError('');

            try {
                const response = await fetch('/api/v1/barcodes/' + currentData.id, {
                    method: 'PUT',
                    headers: authHeaders(),
                    body: JSON.stringify({
                        custom_label: editCustomLabel.value.trim() || null,
                        product_id: editProductId.value || null,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    showEditError(getApiErrorMessage(payload, 'Unable to update barcode.'));
                    return;
                }

                window.location.reload();
            } catch (error) {
                showEditError('Unable to update barcode right now.');
            } finally {
                saveEditBtn.disabled = false;
            }
        }

        async function deleteBarcode() {
            if (!currentData) {
                return;
            }

            confirmDeleteBtn.disabled = true;

            try {
                const response = await fetch('/api/v1/barcodes/' + currentData.id, {
                    method: 'DELETE',
                    headers: authHeaders(),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(getApiErrorMessage(payload, 'Unable to delete barcode.'));
                    return;
                }

                window.location.href = '/barcodes';
            } catch (error) {
                alert('Unable to delete barcode right now.');
            } finally {
                confirmDeleteBtn.disabled = false;
            }
        }

        async function downloadPng() {
            if (!currentData || !currentData.barcode_image_url) {
                return;
            }

            const response = await fetch(currentData.barcode_image_url);
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = currentData.unique_code + '.png';
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () {
                URL.revokeObjectURL(url);
            }, 1000);
        }

        function downloadSvg() {
            if (!currentData || !currentData.barcode_svg) {
                return;
            }

            const blob = new Blob([currentData.barcode_svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = currentData.unique_code + '.svg';
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () {
                URL.revokeObjectURL(url);
            }, 1000);
        }

        editBtn.addEventListener('click', async function () {
            if (!currentData) {
                return;
            }

            showEditError('');
            editBarcodeId.value = currentData.id;
            editCustomLabel.value = currentData.custom_label || '';

            try {
                await loadProducts();
            } catch (error) {
                editProductId.innerHTML = '<option value="">Unable to load products</option>';
            }

            editProductId.value = currentData.product ? currentData.product.id : '';
            editModal.show();
        });

        deleteBtn.addEventListener('click', function () {
            deleteModal.show();
        });

        saveEditBtn.addEventListener('click', saveBarcode);
        confirmDeleteBtn.addEventListener('click', deleteBarcode);
        downloadPngBtn.addEventListener('click', downloadPng);
        downloadSvgBtn.addEventListener('click', downloadSvg);

        loadDetails().catch(function (error) {
            detailLoading.innerHTML = '<div class="card-body p-5 text-center text-danger">' + error.message + '</div>';
        });
    })();
</script>
@endpush
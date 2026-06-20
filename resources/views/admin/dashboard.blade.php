@extends('layouts.admin')

@section('content')
<div class='row g-4 mb-4'>
    <div class='col-sm-6 col-xl-3'>
        <div class='card stat-card border-0 shadow-sm h-100 border-start border-4 border-primary'>
            <div class='card-body'>
                <div class='d-flex align-items-start justify-content-between'>
                    <div>
                        <div class='text-secondary small mb-1'>Total Barcodes Generated</div>
                        <div class='fs-2 fw-bold' id='totalBarcodes'>--</div>
                    </div>
                    <div class='fs-3 text-primary'><i class='bi bi-upc-scan'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-6 col-xl-3'>
        <div class='card stat-card border-0 shadow-sm h-100 border-start border-4 border-success'>
            <div class='card-body'>
                <div class='d-flex align-items-start justify-content-between'>
                    <div>
                        <div class='text-secondary small mb-1'>Total Scans Today</div>
                        <div class='fs-2 fw-bold' id='scansToday'>--</div>
                    </div>
                    <div class='fs-3 text-success'><i class='bi bi-qr-code-scan'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-6 col-xl-3'>
        <div class='card stat-card border-0 shadow-sm h-100 border-start border-4 border-warning'>
            <div class='card-body'>
                <div class='d-flex align-items-start justify-content-between'>
                    <div>
                        <div class='text-secondary small mb-1'>Unique Barcode Data</div>
                        <div class='fs-2 fw-bold' id='uniqueBarcodeData'>--</div>
                    </div>
                    <div class='fs-3 text-warning'><i class='bi bi-box-seam'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-6 col-xl-3'>
        <div class='card stat-card border-0 shadow-sm h-100 border-start border-4 border-purple'>
            <div class='card-body'>
                <div class='d-flex align-items-start justify-content-between'>
                    <div>
                        <div class='text-secondary small mb-1'>Active Users</div>
                        <div class='fs-2 fw-bold' id='activeUsers'>--</div>
                    </div>
                    <div class='fs-3 text-purple'><i class='bi bi-people'></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class='row g-4'>
    <div class='col-12 col-xl-8'>
        <div class='card border-0 shadow-sm h-100'>
            <div class='card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center'>
                <div>
                    <h2 class='h5 mb-0 fw-bold'>Recent Generated Barcodes</h2>
                    <div class='small text-secondary'>Last 10 barcodes generated in the system</div>
                </div>
            </div>
            <div class='card-body p-0'>
                <div id='recentBarcodesLoading' class='p-5 text-center'>
                    <div class='spinner-border text-primary' role='status' aria-hidden='true'></div>
                </div>
                <div class='table-responsive d-none' id='recentBarcodesWrap'>
                    <table class='table table-hover align-middle mb-0'>
                        <thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>Unique Code</th>
                                <th>Format</th>
                                <th>Label</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='recentBarcodesBody'></tbody>
                    </table>
                </div>
                <div id='recentBarcodesEmpty' class='p-4 d-none'>No barcodes found yet.</div>
            </div>
        </div>
    </div>

    <div class='col-12 col-xl-4'>
        <div class='card border-0 shadow-sm mb-4'>
            <div class='card-body'>
                <div class='d-flex align-items-center justify-content-between mb-3'>
                    <div>
                        <h2 class='h5 fw-bold mb-1'>User Login Activity</h2>
                        <div class='small text-secondary'>Current signed-in account</div>
                    </div>
                    <i class='bi bi-person-badge fs-3 text-primary'></i>
                </div>
                <dl class='row mb-0'>
                    <dt class='col-4 text-secondary'>Name</dt>
                    <dd class='col-8 fw-semibold' id='activityName'>--</dd>
                    <dt class='col-4 text-secondary'>Email</dt>
                    <dd class='col-8' id='activityEmail'>--</dd>
                    <dt class='col-4 text-secondary'>Role</dt>
                    <dd class='col-8 text-uppercase fw-semibold' id='activityRole'>--</dd>
                    <dt class='col-4 text-secondary'>Last Login</dt>
                    <dd class='col-8' id='activityLastLogin'>--</dd>
                </dl>
            </div>
        </div>

        <div class='card border-0 shadow-sm'>
            <div class='card-body'>
                <h2 class='h5 fw-bold mb-3'>Quick Actions</h2>
                <div class='d-grid gap-2'>
                    <a href='{{ url('/barcodes/generate') }}' class='btn btn-primary'>Generate Barcode</a>
                    <a href='{{ url('/scanner') }}' class='btn btn-outline-primary'>Open Scanner</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        border-radius: 1rem;
        overflow: hidden;
    }

    .border-purple {
        border-color: #8b5cf6 !important;
    }

    .text-purple {
        color: #8b5cf6 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function formatDateTime(value) {
        if (! value) {
            return 'N/A';
        }
        return new Date(value).toLocaleString();
    }

    function safeUser() {
        try {
            return JSON.parse(localStorage.getItem('auth_user') || 'null');
        } catch (error) {
            return null;
        }
    }

    async function loadDashboardStats() {
        try {
            const response = await fetch('/api/v1/dashboard/stats', {
                headers: setAuthHeaders(),
            });
            const payload = await response.json();

            if (! response.ok) {
                throw new Error(getApiErrorMessage(payload));
            }

            document.getElementById('totalBarcodes').textContent = payload.data.total_barcodes;
            document.getElementById('scansToday').textContent = payload.data.scans_today;
            document.getElementById('uniqueBarcodeData').textContent = payload.data.unique_barcode_data;
            document.getElementById('activeUsers').textContent = payload.data.active_users;
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
        }
    }

    async function loadRecentBarcodes() {
        const loading = document.getElementById('recentBarcodesLoading');
        const wrap = document.getElementById('recentBarcodesWrap');
        const empty = document.getElementById('recentBarcodesEmpty');
        const body = document.getElementById('recentBarcodesBody');

        try {
            const response = await fetch('/api/v1/dashboard/recent-barcodes', {
                headers: setAuthHeaders(),
            });
            const payload = await response.json();

            if (! response.ok) {
                throw new Error(getApiErrorMessage(payload));
            }

            const rows = payload.data.data || [];
            body.innerHTML = '';

            if (! rows.length) {
                loading.classList.add('d-none');
                empty.classList.remove('d-none');
                return;
            }

            rows.forEach(function (item, index) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td class='fw-semibold'>${item.unique_code || '--'}</td>
                    <td><span class='badge text-bg-light border'>${item.barcode_format || 'N/A'}</span></td>
                    <td>${item.custom_label || '—'}</td>
                    <td>${item.created_at ? formatDateTime(item.created_at) : 'N/A'}</td>
                    <td><a href='/barcodes/${item.id}' class='btn btn-sm btn-outline-primary'>View</a></td>
                `;
                body.appendChild(tr);
            });

            loading.classList.add('d-none');
            wrap.classList.remove('d-none');
        } catch (error) {
            console.error('Failed to load recent barcodes:', error);
            loading.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.textContent = 'Unable to load recent barcodes right now.';
        }
    }

    async function loadCurrentUser() {
        try {
            const user = safeUser();

            if (user) {
                document.getElementById('headerUserName').textContent = user.name || 'Admin';
                document.getElementById('activityName').textContent = user.name || 'N/A';
                document.getElementById('activityEmail').textContent = user.email || 'N/A';
                document.getElementById('activityRole').textContent = (user.role || 'admin').toString();
            }

            const response = await fetch('/api/v1/auth/me', {
                headers: setAuthHeaders(),
            });
            const payload = await response.json();

            if (! response.ok) {
                throw new Error(getApiErrorMessage(payload));
            }

            const current = payload.data.user;
            document.getElementById('activityName').textContent = current.name || 'N/A';
            document.getElementById('activityEmail').textContent = current.email || 'N/A';
            document.getElementById('activityRole').textContent = (current.role || 'admin').toString();
            document.getElementById('activityLastLogin').textContent = formatDateTime(current.last_login_at);
            document.getElementById('headerUserName').textContent = current.name || 'Admin';
            document.getElementById('headerUserRole').textContent = (current.role || 'admin').toString().toUpperCase();

            localStorage.setItem('auth_user', JSON.stringify(current));
        } catch (error) {
            console.error('Failed to load current user:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', async function () {
        await Promise.allSettled([
            loadDashboardStats(),
            loadRecentBarcodes(),
            loadCurrentUser(),
        ]);
    });
</script>
@endpush


<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='csrf-token' content='{{ csrf_token() }}'>
    <title>{{ config('app.name', 'BarcodeMS') }} - Admin</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH' crossorigin='anonymous'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
    @stack('styles')
    <style>
        body.admin-shell {
            background: #f1f5f9;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 250px;
            background: #1e293b;
            color: #fff;
            z-index: 1030;
        }

        .admin-main {
            margin-left: 250px;
            min-height: 100vh;
            background: #f1f5f9;
        }

        .admin-header {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .sidebar-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.85rem;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar-logout {
            background: transparent;
            border: 0;
            color: rgba(255, 255, 255, 0.8);
            text-align: left;
            width: 100%;
        }

        .sidebar-logout:hover {
            color: #fff;
            background: rgba(239, 68, 68, 0.18);
        }
    </style>
</head>
<body class='admin-shell'>
    @include('partials.auth-check')
    @include('partials.admin-check')

    <aside class='admin-sidebar d-flex flex-column p-3 p-lg-4'>
        <div class='mb-4'>
            <a href='{{ url('/dashboard') }}' class='text-decoration-none text-white d-block'>
                <div class='d-flex align-items-center gap-2'>
                    <span class='fs-3'>?</span>
                    <div>
                        <div class='fw-bold fs-5'>BarcodeMS</div>
                        <div class='small text-white-50'>Admin Panel</div>
                    </div>
                </div>
            </a>
        </div>

        <nav class='nav flex-column gap-2 flex-grow-1'>
            <a href='{{ url('/dashboard') }}' class='nav-link sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}'>
                <i class='bi bi-house-door me-2'></i>Dashboard
            </a>
            <a href='{{ url('/barcodes/generate') }}' class='nav-link sidebar-link {{ request()->is('barcodes/generate') ? 'active' : '' }}'>
                <i class='bi bi-plus-circle me-2'></i>Generate Barcode
            </a>
            <a href='{{ url('/barcodes') }}' class='nav-link sidebar-link {{ request()->is('barcodes') ? 'active' : '' }}'>
                <i class='bi bi-list-check me-2'></i>Barcodes List
            </a>
            <a href='{{ url('/scanner') }}' class='nav-link sidebar-link {{ request()->is('scanner') ? 'active' : '' }}'>
                <i class='bi bi-camera me-2'></i>Scanner
            </a>
        </nav>

        <div class='pt-3 border-top border-white border-opacity-10'>
            <button type='button' id='sidebarLogoutBtn' class='sidebar-logout rounded-3 px-3 py-2'>
                <i class='bi bi-box-arrow-right me-2'></i>Logout
            </button>
        </div>
    </aside>

    <main class='admin-main'>
        <header class='admin-header bg-white shadow-sm'>
            <div class='d-flex justify-content-between align-items-center px-4 px-lg-5 py-3'>
                <div>
                    <div class='text-secondary small'>Admin Dashboard</div>
                    <div class='fw-semibold'>Welcome back, <span id='headerUserName'>Admin</span></div>
                </div>
                <div class='d-flex align-items-center gap-3'>
                    <div class='text-end d-none d-md-block'>
                        <div class='fw-semibold' id='headerUserRole'>Admin</div>
                        <div class='small text-secondary'>Logged in</div>
                    </div>
                    <button type='button' id='headerLogoutBtn' class='btn btn-outline-danger'>
                        <i class='bi bi-box-arrow-right me-1'></i>Logout
                    </button>
                </div>
            </div>
        </header>

        <div class='p-4 p-lg-5'>
            @yield('content')
        </div>
    </main>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js' integrity='sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1N7N6jIeHz' crossorigin='anonymous'></script>
    <script>
        (function () {
            var user = window.currentAuthUser || null;
            var headerUserName = document.getElementById('headerUserName');
            var headerUserRole = document.getElementById('headerUserRole');

            if (user) {
                headerUserName.textContent = user.name || 'Admin';
                headerUserRole.textContent = (user.role || 'admin').toString().toUpperCase();
            }

            async function logout() {
                try {
                    await fetch('/api/v1/auth/logout', {
                        method: 'POST',
                        headers: setAuthHeaders(),
                    });
                } catch (error) {
                    // ignore logout transport errors
                }

                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                window.location.replace('/login');
            }

            document.getElementById('headerLogoutBtn').addEventListener('click', logout);
            document.getElementById('sidebarLogoutBtn').addEventListener('click', logout);
        })();
    </script>
    @stack('scripts')
</body>
</html>

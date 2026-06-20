<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='csrf-token' content='{{ csrf_token() }}'>
    <title>{{ config('app.name', 'BarcodeMS') }}</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH' crossorigin='anonymous'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
    <style>
        body.auth-shell {
            background: radial-gradient(circle at top, rgba(74, 144, 226, 0.18), transparent 34%), #1a1a2e;
        }

        .auth-wrap {
            max-width: 520px;
        }

        .auth-card {
            background: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body class='auth-shell'>
    <main class='container min-vh-100 d-flex align-items-center justify-content-center py-5'>
        <div class='w-100 auth-wrap'>
            <div class='text-center text-white mb-4'>
                <a href='{{ url('/') }}' class='text-decoration-none text-white'>
                    <h1 class='display-6 fw-bold mb-0'>BarcodeMS</h1>
                </a>
            </div>
            <div class='card border-0 shadow-lg rounded-4 auth-card mx-auto'>
                <div class='card-body p-4 p-md-5'>
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js' integrity='sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1N7N6jIeHz' crossorigin='anonymous'></script>
    @stack('scripts')
</body>
</html>

<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='csrf-token' content='{{ csrf_token() }}'>
    <title>{{ config('app.name', 'Barcode') }}</title>
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

        .auth-logo-mark {
            width: 3rem;
            height: 3rem;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem;
            background: rgba(255, 255, 255, 0.10);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        }

        .auth-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        @media (max-width: 575.98px) {
            .auth-wrap {
                max-width: 100%;
            }

            .auth-shell .display-6 {
                font-size: 1.8rem;
            }

            .auth-card .card-body {
                padding: 1.25rem !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class='auth-shell'>
    <main class='container min-vh-100 d-flex align-items-center justify-content-center py-4 py-md-5 px-3'>
        <div class='w-100 auth-wrap'>
            <div class='text-center text-white mb-4'>
                <a href='{{ url('/') }}' class='text-decoration-none text-white'>
                    <div class='d-inline-flex align-items-center gap-3'>
                        <span class='auth-logo-mark'>
                            <img src='{{ asset("logo.png") }}' alt='Barcode logo' class='auth-logo-img'>
                        </span>
                        <h1 class='display-6 fw-bold mb-0'>Barcode</h1>
                    </div>
                </a>
            </div>
            <div class='card border-0 shadow-lg rounded-4 auth-card mx-auto'>
                <div class='card-body p-4 p-md-5'>
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    @include('partials.auth-check')
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js' integrity='sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1N7N6jIeHz' crossorigin='anonymous'></script>
    @stack('scripts')
</body>
</html>


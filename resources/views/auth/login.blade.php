@extends('layouts.auth')

@section('content')
<div class='text-center mb-4'>
    <h2 class='fw-bold mb-2'>Welcome Back</h2>
    <p class='text-secondary mb-0'>Log in to access your barcode tools.</p>
</div>

<div id='loginError' class='alert alert-danger d-none' role='alert'></div>

<form id='loginForm' class='vstack gap-3'>
    <div>
        <label for='email' class='form-label'>Email</label>
        <input type='email' id='email' class='form-control form-control-lg' required>
    </div>

    <div>
        <label for='password' class='form-label'>Password</label>
        <div class='input-group input-group-lg'>
            <input type='password' id='password' class='form-control' required>
            <button class='btn btn-outline-secondary' type='button' data-toggle-password='password' aria-label='Toggle password visibility'>
                <i class='bi bi-eye'></i>
            </button>
        </div>
    </div>

    <div class='form-check'>
        <input class='form-check-input' type='checkbox' value='1' id='rememberMe'>
        <label class='form-check-label' for='rememberMe'>Remember Me</label>
    </div>

    <button type='submit' class='btn btn-primary btn-lg w-100'>Login</button>
</form>

<div class='d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4 small'>
    <a href='{{ url('/forgot-password') }}' class='text-decoration-none'>Forgot Password?</a>
    <a href='{{ url('/register') }}' class='text-decoration-none'>Don't have an account? Register</a>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            const icon = button.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
</script>
@endpush

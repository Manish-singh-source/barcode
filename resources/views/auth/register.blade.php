@extends('layouts.auth')

@section('content')
<div class='text-center mb-4'>
    <h2 class='fw-bold mb-2'>Create Account</h2>
    <p class='text-secondary mb-0'>Join BarcodeMS and get started.</p>
</div>

<div id='registerError' class='alert alert-danger d-none' role='alert'></div>

<form id='registerForm' class='vstack gap-3'>
    <div>
        <label for='name' class='form-label'>Full Name</label>
        <input type='text' id='name' class='form-control form-control-lg' required>
    </div>

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

    <div>
        <label for='password_confirmation' class='form-label'>Confirm Password</label>
        <div class='input-group input-group-lg'>
            <input type='password' id='password_confirmation' class='form-control' required>
            <button class='btn btn-outline-secondary' type='button' data-toggle-password='password_confirmation' aria-label='Toggle password visibility'>
                <i class='bi bi-eye'></i>
            </button>
        </div>
    </div>

    <button type='submit' class='btn btn-primary btn-lg w-100'>Register</button>
</form>

<div class='mt-4 small text-center'>
    <a href='{{ url('/login') }}' class='text-decoration-none'>Already have an account? Login</a>
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

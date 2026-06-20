@extends('layouts.auth')

@section('content')
<div class='text-center mb-4'>
    <h2 class='fw-bold mb-2'>Reset Password</h2>
    <p class='text-secondary mb-0'>Enter your email and we'll send you a reset link.</p>
</div>

<div id='forgotSuccess' class='alert alert-success d-none' role='alert'></div>
<div id='forgotError' class='alert alert-danger d-none' role='alert'></div>

<form id='forgotForm' class='vstack gap-3'>
    <div>
        <label for='email' class='form-label'>Email</label>
        <input type='email' id='email' class='form-control form-control-lg' required>
    </div>

    <button type='submit' class='btn btn-primary btn-lg w-100'>Send Reset Link</button>
</form>

<div class='mt-4 small text-center'>
    <a href='{{ url('/login') }}' class='text-decoration-none'>Back to Login</a>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('forgotForm').addEventListener('submit', async function (event) {
        event.preventDefault();

        var successBox = document.getElementById('forgotSuccess');
        var errorBox = document.getElementById('forgotError');
        successBox.classList.add('d-none');
        errorBox.classList.add('d-none');
        successBox.textContent = '';
        errorBox.textContent = '';

        try {
            var response = await fetch('/api/v1/auth/forgot-password', {
                method: 'POST',
                headers: setAuthHeaders(),
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                }),
            });

            var payload = await response.json();

            if (!response.ok) {
                errorBox.textContent = getApiErrorMessage(payload);
                errorBox.classList.remove('d-none');
                return;
            }

            successBox.textContent = 'Reset link sent! Check your email.';
            successBox.classList.remove('d-none');
        } catch (error) {
            errorBox.textContent = 'Unable to send reset link right now.';
            errorBox.classList.remove('d-none');
        }
    });
</script>
@endpush

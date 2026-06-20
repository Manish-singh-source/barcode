<script>
    window.setAuthHeaders = function (extraHeaders) {
        var headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };

        var token = localStorage.getItem('auth_token');
        if (token) {
            headers.Authorization = 'Bearer ' + token;
        }

        if (extraHeaders) {
            Object.keys(extraHeaders).forEach(function (key) {
                headers[key] = extraHeaders[key];
            });
        }

        return headers;
    };

    window.getApiErrorMessage = function (payload) {
        if (! payload) {
            return 'Something went wrong.';
        }

        if (payload.message) {
            return payload.message;
        }

        if (payload.errors) {
            var messages = [];
            Object.keys(payload.errors).forEach(function (field) {
                payload.errors[field].forEach(function (message) {
                    messages.push(message);
                });
            });
            return messages.join(' ');
        }

        return 'Something went wrong.';
    };

    if ((window.location.pathname === '/login' || window.location.pathname === '/register') && localStorage.getItem('auth_token')) {
        window.location.href = '/dashboard';
    }
</script>

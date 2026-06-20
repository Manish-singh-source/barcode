<script>
(function () {
    const loginPaths = ['/login', '/register'];
    const currentPath = (window.location.pathname || '/').replace(/\/+$/, '') || '/';

    function clearAuthSession() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
    }

    function getStoredToken() {
        return localStorage.getItem('auth_token');
    }

    function getStoredUser() {
        try {
            return JSON.parse(localStorage.getItem('auth_user') || 'null');
        } catch (error) {
            return null;
        }
    }

    function setAuthHeaders(extraHeaders = {}) {
        const headers = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...extraHeaders,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const token = getStoredToken();
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        return headers;
    }

    function getApiErrorMessage(payload, fallback = 'Something went wrong. Please try again.') {
        if (!payload) {
            return fallback;
        }

        if (typeof payload.message === 'string' && payload.message.trim() !== '') {
            return payload.message;
        }

        if (payload.errors && typeof payload.errors === 'object') {
            const firstErrorGroup = Object.values(payload.errors).find((value) => Array.isArray(value) && value.length > 0);
            if (firstErrorGroup) {
                return firstErrorGroup[0];
            }
        }

        return fallback;
    }

    async function validateStoredSession() {
        const token = getStoredToken();
        if (!token) {
            return null;
        }

        try {
            const response = await fetch('/api/v1/auth/me', {
                method: 'GET',
                headers: setAuthHeaders(),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(getApiErrorMessage(payload, 'Session expired.'));
            }

            const user = payload?.data?.user || payload?.data || null;
            if (user) {
                localStorage.setItem('auth_user', JSON.stringify(user));
            }

            return user;
        } catch (error) {
            clearAuthSession();
            return null;
        }
    }

    window.clearAuthSession = clearAuthSession;
    window.setAuthHeaders = setAuthHeaders;
    window.getApiErrorMessage = getApiErrorMessage;
    window.validateStoredSession = validateStoredSession;
    window.getStoredAuthUser = getStoredUser;

    if (loginPaths.includes(currentPath)) {
        validateStoredSession().then((user) => {
            if (!user) {
                return;
            }

            if ((user.role || '') === 'admin') {
                window.location.replace('/dashboard');
                return;
            }

            window.location.replace('/');
        });
    }
})();
</script>

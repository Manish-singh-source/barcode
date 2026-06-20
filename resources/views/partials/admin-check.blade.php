<script>
(function () {
    const protectedPaths = [
        '/dashboard',
        '/barcodes',
        '/barcodes/generate',
        '/scanner',
    ];
    const currentPath = (window.location.pathname || '/').replace(/\/+$/, '') || '/';

    async function validateAdminSession() {
        if (typeof window.validateStoredSession !== 'function') {
            return null;
        }

        const user = await window.validateStoredSession();
        if (!user) {
            window.location.replace('/login');
            return null;
        }

        if ((user.role || '') !== 'admin') {
            window.location.replace('/');
            return null;
        }

        window.currentAuthUser = user;
        return user;
    }

    if (protectedPaths.some((path) => currentPath === path || currentPath.startsWith(path + '/'))) {
        validateAdminSession();
    }
})();
</script>
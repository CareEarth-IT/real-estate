<script>
    window.adminAppBasePath = function () {
        const path = window.location.pathname.replace(/\/$/, '');
        const markers = [
            '/property/rental-income',
            '/property/deal-drafts',
            '/properties',
            '/reference',
            '/users',
        ];

        for (const marker of markers) {
            if (path === marker || path.endsWith(marker)) {
                return path.slice(0, path.length - marker.length);
            }

            const markerIndex = path.indexOf(marker + '/');
            if (markerIndex !== -1) {
                return path.slice(0, markerIndex);
            }
        }

        return '';
    };

    window.adminApiUrl = function (path) {
        const normalizedPath = path.startsWith('/') ? path : `/${path}`;

        return window.adminAppBasePath() + normalizedPath;
    };
</script>

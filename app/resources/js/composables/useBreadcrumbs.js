import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';

const dynamicBreadcrumb = ref(null);

export function useBreadcrumbs() {
    const route = useRoute();
    const breadcrumbs = ref([]);

    function generateBreadcrumbs() {
        const crumbs = [];
        const matched = route.matched;
        const isAdminRoute = route.path.startsWith('/admin');
        const isDashboard = route.name === 'admin.dashboard';

        // For admin routes that are not dashboard, always add Dashboard as first breadcrumb
        if (isAdminRoute && !isDashboard) {
            crumbs.push({
                label: 'Dashboard',
                to: '/admin/dashboard',
            });
        }

        // Build breadcrumbs from matched routes
        for (let i = 0; i < matched.length; i++) {
            const routeRecord = matched[i];
            const meta = routeRecord.meta;
            
            // Skip routes without breadcrumb metadata
            if (!meta?.breadcrumb) {
                continue;
            }

            // Handle dynamic breadcrumbs
            if (meta.breadcrumb === 'dynamic') {
                if (dynamicBreadcrumb.value) {
                    crumbs.push({
                        label: dynamicBreadcrumb.value,
                        to: null, // Current page, no link
                    });
                }
                continue;
            }

            // Determine if this breadcrumb should be a link
            let breadcrumbPath = null;
            const isCurrentPage = i === matched.length - 1;
            
            if (!isCurrentPage) {
                // This is a parent/intermediate page, make it a link
                // Construct the full path by combining parent paths
                const paths = matched.slice(0, i + 1).map(r => r.path);
                let fullPath = '';
                
                for (const p of paths) {
                    if (p.startsWith('/')) {
                        fullPath = p;
                    } else if (p) {
                        fullPath = fullPath.endsWith('/') ? fullPath + p : fullPath + '/' + p;
                    }
                }
                
                // Only link if it's a valid path without params
                if (fullPath && !fullPath.includes(':')) {
                    breadcrumbPath = fullPath;
                }
            }

            crumbs.push({
                label: meta.breadcrumb,
                to: breadcrumbPath,
            });
        }

        breadcrumbs.value = crumbs;
    }

    function setDynamicBreadcrumb(label) {
        dynamicBreadcrumb.value = label;
        generateBreadcrumbs();
    }

    function clearDynamicBreadcrumb() {
        dynamicBreadcrumb.value = null;
    }

    // Generate breadcrumbs on route change
    watch(
        () => route.path,
        () => {
            clearDynamicBreadcrumb();
            generateBreadcrumbs();
        },
        { immediate: true }
    );

    // Regenerate when dynamic breadcrumb changes
    watch(dynamicBreadcrumb, generateBreadcrumbs);

    return {
        breadcrumbs,
        setDynamicBreadcrumb,
        clearDynamicBreadcrumb,
    };
}


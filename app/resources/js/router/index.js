import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../views/HomeView.vue'),
        meta: {
            title: 'Home',
            layout: 'default',
        },
    },
    {
        path: '/category/:slug',
        name: 'category',
        component: () => import('../views/CategoryView.vue'),
        meta: {
            title: 'Category',
            layout: 'default',
        },
    },
    {
        path: '/product/:slug',
        name: 'product',
        component: () => import('../views/ProductView.vue'),
        meta: {
            title: 'Product',
            layout: 'default',
        },
    },
    {
        path: '/admin/login',
        name: 'admin.login',
        component: () => import('../views/admin/AdminLoginView.vue'),
        meta: {
            title: 'Admin Login',
            layout: 'auth',
            guestOnly: true,
        },
    },
    {
        path: '/admin',
        redirect: { name: 'admin.dashboard' },
        meta: {
            requiresAuth: true,
            requiresPermission: 'catalog.view',
            layout: 'admin',
            title: 'Admin',
        },
        children: [
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: () => import('../views/admin/DashboardView.vue'),
                meta: {
                    title: 'Dashboard',
                    breadcrumb: 'Dashboard',
                },
            },
            {
                path: 'products',
                name: 'admin.products.index',
                component: () => import('../views/admin/products/ProductIndexView.vue'),
                meta: {
                    title: 'Products',
                    breadcrumb: 'Products',
                    requiresPermission: 'catalog.view',
                },
            },
            {
                path: 'products/:id',
                name: 'admin.products.show',
                component: () => import('../views/admin/products/ProductDetailView.vue'),
                meta: {
                    title: 'Product Details',
                    breadcrumb: 'dynamic',
                    requiresPermission: 'catalog.manage',
                },
            },
            {
                path: 'categories',
                name: 'admin.categories.index',
                component: () => import('../views/admin/categories/CategoryIndexView.vue'),
                meta: {
                    title: 'Categories',
                    breadcrumb: 'Categories',
                    requiresPermission: 'catalog.view',
                },
            },
            {
                path: 'brands',
                name: 'admin.brands.index',
                component: () => import('../views/admin/brands/BrandIndexView.vue'),
                meta: {
                    title: 'Brands',
                    breadcrumb: 'Brands',
                    requiresPermission: 'catalog.view',
                },
            },
            {
                path: 'attributes',
                name: 'admin.attributes.index',
                component: () => import('../views/admin/attributes/AttributeIndexView.vue'),
                meta: {
                    title: 'Attributes',
                    breadcrumb: 'Attributes',
                    requiresPermission: 'catalog.view',
                },
            },
            {
                path: 'settings',
                name: 'admin.settings',
                component: () => import('../views/admin/settings/SettingsView.vue'),
                meta: {
                    title: 'Settings',
                    breadcrumb: 'Settings',
                    requiresPermission: 'catalog.view',
                },
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0, behavior: 'smooth' };
    },
});

router.afterEach((to) => {
    const appName = import.meta.env.VITE_APP_NAME;
    if (to.meta?.title) {
        document.title = `${to.meta.title} | ${appName}`;
    } else {
        document.title = appName;
    }
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.ensureSession();
    }

    if (to.meta?.requiresAuth && !auth.isAuthenticated) {
        return next({
            name: 'admin.login',
            query: { redirect: to.fullPath },
        });
    }

    if (to.meta?.guestOnly && auth.isAuthenticated) {
        return next({ name: 'admin.dashboard' });
    }

    const requiredPermission = to.meta?.requiresPermission;
    if (requiredPermission && !auth.hasPermission(requiredPermission)) {
        return next({ name: 'admin.dashboard' });
    }

    return next();
});

export default router;

import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../views/HomeView.vue'),
        meta: {
            title: 'Home',
        },
    },
    {
        path: '/category/:slug',
        name: 'category',
        component: () => import('../views/CategoryView.vue'),
        meta: {
            title: 'Category',
        },
    },
    {
        path: '/product/:slug',
        name: 'product',
        component: () => import('../views/ProductView.vue'),
        meta: {
            title: 'Product',
        },
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

export default router;

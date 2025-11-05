<template>
    <div class="space-y-8">
        <section>
            <h1 class="text-2xl font-semibold text-slate-900">Backoffice Overview</h1>
            <p class="text-sm text-slate-500">Monitor catalog health, editorial workload, and product pipeline at a glance.</p>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="metric in metrics"
                :key="metric.label"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ metric.label }}</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600">
                        <component :is="metric.icon" class="h-6 w-6" />
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">{{ metric.caption }}</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Latest products</h2>
                        <p class="text-sm text-slate-500">Recently updated SKUs ready for storefront review.</p>
                    </div>
                    <router-link
                        :to="{ name: 'admin.products.index' }"
                        class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 hover:text-sky-500"
                    >
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </router-link>
                </div>

                <div v-if="latestProducts.length" class="mt-4 divide-y divide-slate-100">
                    <article v-for="product in latestProducts" :key="product.id" class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ product.name }}</p>
                            <p class="text-xs text-slate-500">{{ product.brand?.name ?? 'Unbranded' }} &middot; Updated {{ formatDate(product.updated_at) }}</p>
                        </div>
                        <router-link
                            :to="{ name: 'admin.products.show', params: { id: product.id } }"
                            class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500 hover:border-sky-200 hover:text-sky-600"
                        >
                            Manage
                        </router-link>
                    </article>
                </div>

                <div v-else class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    No products found. Seed the catalog to get started.
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Your access</h2>
                <p class="text-sm text-slate-500">Permissions granted to this session via RBAC.</p>

                <ul class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <li
                        v-for="permission in auth.permissions"
                        :key="permission"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"
                    >
                        {{ permission }}
                    </li>
                </ul>

                <div v-if="!auth.permissions.length" class="mt-4 rounded-xl border border-dashed border-amber-200 bg-amber-50 px-3 py-4 text-sm text-amber-600">
                    This account currently has no permissions. Contact an administrator to assign the correct role.
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { catalogApi } from '@/services/admin/catalog';

const auth = useAuthStore();

const brandCount = ref(0);
const categoryCount = ref(0);
const productCount = ref(0);
const attributeCount = ref(0);
const latestProducts = ref([]);

const icons = {
    cube: defineComponent({
        name: 'CubeIcon',
        render() {
            return h('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm20.25 7.5-8.25 4.5-8.25-4.5m16.5 0L12 3m8.25 4.5v9l-8.25 4.5m0-9L3.75 7.5m8.25 4.5v9m0-18-8.25 4.5v9l8.25 4.5' }),
            ]);
        },
    }),
    folder: defineComponent({
        name: 'FolderIcon',
        render() {
            return h('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3 7.5a2.25 2.25 0 0 1 2.25-2.25h4.318a2.25 2.25 0 0 1 1.591.659l1.341 1.341a2.25 2.25 0 0 0 1.591.659H18.75A2.25 2.25 0 0 1 21 10.5v6.75A2.25 2.25 0 0 1 18.75 19.5H5.25A2.25 2.25 0 0 1 3 17.25v-9.75Z' }),
            ]);
        },
    }),
    sparkles: defineComponent({
        name: 'SparklesIcon',
        render() {
            return h('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.813 15.904 9 18.75l-.813-2.846a3 3 0 0 0-2.091-2.091L3.25 13l2.846-.813a3 3 0 0 0 2.091-2.091L9 7.25l.813 2.846a3 3 0 0 0 2.091 2.091l2.846.813-2.846.813a3 3 0 0 0-2.091 2.091ZM18 5.25l.469 1.641a2 2 0 0 0 1.39 1.39L21.5 8.75l-1.641.469a2 2 0 0 0-1.39 1.39L18 12.25l-.469-1.641a2 2 0 0 0-1.39-1.39L14.5 8.75l1.641-.469a2 2 0 0 0 1.39-1.39L18 5.25Z' }),
            ]);
        },
    }),
    adjustments: defineComponent({
        name: 'AdjustmentsIcon',
        render() {
            return h('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M10.5 6h9M4.5 6h3m3 12h9m-13.5 0h3m6-6h6m-13.5 0h6' }),
            ]);
        },
    }),
};

const metrics = computed(() => [
    {
        label: 'Published products',
        value: productCount.value,
        caption: 'Active SKUs synchronized across storefront experiences.',
        icon: icons.cube,
    },
    {
        label: 'Live categories',
        value: categoryCount.value,
        caption: 'Navigation-ready groupings curated for conversion.',
        icon: icons.folder,
    },
    {
        label: 'Partner brands',
        value: brandCount.value,
        caption: 'Manufacturer partnerships with merchandising rights.',
        icon: icons.sparkles,
    },
    {
        label: 'Attribute facets',
        value: attributeCount.value,
        caption: 'Structured specs powering PDP content and filters.',
        icon: icons.adjustments,
    },
]);

onMounted(async () => {
    await Promise.all([
        loadProductMetrics(),
        loadCategoryMetrics(),
        loadBrandMetrics(),
        loadAttributeMetrics(),
    ]);

    await loadLatestProducts();
});

async function loadProductMetrics() {
    const response = await catalogApi.listProducts({ per_page: 1 });
    productCount.value = response.data?.meta?.total ?? response.data?.data?.length ?? 0;
}

async function loadCategoryMetrics() {
    const response = await catalogApi.listCategories({ per_page: 1 });
    categoryCount.value = response.data?.meta?.total ?? response.data?.data?.length ?? 0;
}

async function loadBrandMetrics() {
    const response = await catalogApi.listBrands({ per_page: 1 });
    brandCount.value = response.data?.meta?.total ?? response.data?.data?.length ?? 0;
}

async function loadAttributeMetrics() {
    const response = await catalogApi.listAttributes({ per_page: 1 });
    attributeCount.value = response.data?.meta?.total ?? response.data?.data?.length ?? 0;
}

async function loadLatestProducts() {
    const response = await catalogApi.listProducts({ per_page: 5, include: ['brand'] });
    latestProducts.value = response.data?.data ?? [];
}

function formatDate(timestamp) {
    if (!timestamp) {
        return 'just now';
    }

    const diffDays = Math.round((new Date(timestamp) - new Date()) / (1000 * 60 * 60 * 24));

    if (Number.isNaN(diffDays)) {
        return 'recently';
    }

    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    return rtf.format(diffDays, 'day');
}
</script>

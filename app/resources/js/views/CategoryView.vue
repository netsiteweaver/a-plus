<template>
    <div v-if="!loading && category" class="space-y-10">
        <Breadcrumbs :items="breadcrumbs" />

        <header class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold text-slate-900 capitalize">{{ category.name }}</h1>
                    <p class="max-w-2xl text-sm text-slate-600">{{ category.description }}</p>
                </div>
                <div class="flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-slate-400">
                    <span>{{ products.length }} products</span>
                    <span class="hidden h-0.5 w-8 bg-slate-200 md:block"></span>
                    <button class="rounded-full border border-slate-200 px-4 py-1.5 text-xs text-slate-600 transition hover:border-blue-400/60 hover:text-blue-600">
                        Export specs
                    </button>
                </div>
            </div>
        </header>

        <div class="grid gap-10 lg:grid-cols-12">
            <aside class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Filters</span>
                    <button class="text-xs uppercase tracking-[0.25em] text-blue-600 transition hover:text-blue-700" @click="resetFilters">Reset</button>
                </div>
                <div class="space-y-6 text-sm text-slate-600">
                    <details v-for="filter in filters" :key="filter.key" open class="rounded-2xl border border-slate-200 bg-slate-50">
                        <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                            {{ filter.label }}
                            <span class="text-slate-300">▾</span>
                        </summary>
                        <div class="space-y-3 border-t border-slate-200 px-4 py-4" v-if="filter.options">
                            <label
                                v-for="option in filter.options"
                                :key="option.value"
                                class="flex items-center justify-between gap-4 text-xs text-slate-600"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500" />
                                    {{ option.label }}
                                </span>
                                <span class="text-slate-400">{{ option.count }}</span>
                            </label>
                        </div>
                        <div class="space-y-2 border-t border-slate-200 px-4 py-4 text-xs text-slate-500" v-else-if="filter.range">
                            <p>Min: {{ formatCurrency(filter.range.min) }}</p>
                            <p>Max: {{ formatCurrency(filter.range.max) }}</p>
                            <p class="text-slate-400">Interactive range slider coming soon.</p>
                        </div>
                    </details>
                </div>
            </aside>

            <section class="space-y-6 lg:col-span-9">
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-xs uppercase tracking-[0.28em] text-slate-400">Sort by</span>
                        <button class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-blue-400/60 hover:text-blue-600">
                            Top rated
                        </button>
                        <button class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-blue-400/60 hover:text-blue-600">
                            Newest
                        </button>
                        <button class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-blue-400/60 hover:text-blue-600">
                            Price - Low to high
                        </button>
                    </div>
                    <span class="text-xs uppercase tracking-[0.25em] text-slate-400">Compare (0)</span>
                </div>

                <ProductGrid :products="products" dense />
            </section>
        </div>
    </div>
    <div v-else class="py-24 text-center text-sm uppercase tracking-[0.3em] text-slate-400">Loading catalog...</div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import ProductGrid from '@/components/product/ProductGrid.vue';

const route = useRoute();
const loading = ref(true);
const category = ref(null);
const products = ref([]);
const filters = ref([]);

const fetchCategory = async () => {
    loading.value = true;
    try {
        const slug = route.params.slug?.toString() ?? 'laptops';
        const { data } = await axios.get(`/api/catalog/categories/${slug}`);
        category.value = data.category;
        products.value = data.products ?? [];
        filters.value = data.filters ?? [];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchCategory);

watch(
    () => route.params.slug,
    () => {
        fetchCategory();
    }
);

const categoryName = computed(() => category.value?.name ?? 'Category');

const breadcrumbs = computed(() => [
    { label: 'Home', to: '/' },
    { label: 'Catalog', to: '/category/laptops' },
    { label: categoryName.value, to: route.fullPath },
]);

const resetFilters = () => {
    // placeholder for future interactive filtering
};

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value ?? 0);
</script>

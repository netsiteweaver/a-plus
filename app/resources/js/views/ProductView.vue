<template>
    <div v-if="!loading && product" class="space-y-12">
        <Breadcrumbs :items="breadcrumbs" />

        <div class="grid gap-10 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-5">
                <ProductMediaGallery :media-items="mediaItems" />
            </div>

            <section class="space-y-8 lg:col-span-7">
                <header class="space-y-4">
                    <div class="flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-sky-600">
                        <span>{{ product.badge ?? 'New arrival' }}</span>
                        <span class="hidden h-0.5 w-8 bg-sky-500/20 md:block"></span>
                        <span>{{ product.brand }}</span>
                    </div>
                    <h1 class="text-3xl font-semibold text-slate-800">{{ product.name }}</h1>
                    <p class="text-sm text-slate-500">{{ product.subtitle }}</p>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                        <span class="inline-flex items-center gap-2 text-slate-900">
                            <span class="text-xl font-semibold">{{ formatCurrency(product.price) }}</span>
                            <span v-if="product.compare_at_price" class="text-sm text-slate-400 line-through">{{ formatCurrency(product.compare_at_price) }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2 text-sky-600">
                            * {{ product.rating.toFixed(1) }}
                            <span class="text-slate-400">({{ product.rating_count }} reviews)</span>
                        </span>
                        <span class="text-slate-400">SKU {{ product.sku }}</span>
                    </div>
                </header>

                <div class="space-y-6">
                    <div v-for="option in options" :key="option.code" class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ option.name }}</p>
                        <div v-if="option.code === 'color'" class="flex flex-wrap gap-2">
                            <button
                                v-for="value in option.values"
                                :key="value.id"
                                type="button"
                                :style="{ backgroundColor: value.hex_value ?? '#1e293b' }"
                                class="h-10 w-10 rounded-full border border-slate-200 transition hover:border-sky-400"
                                :class="selectedColor === value.value ? 'ring-2 ring-sky-400' : ''"
                                @click="selectedColor = value.value"
                            >
                                <span class="sr-only">{{ value.display_value }}</span>
                            </button>
                        </div>
                        <div v-else class="flex flex-wrap gap-2">
                            <button
                                v-for="value in option.values"
                                :key="value.id"
                                type="button"
                                class="rounded-full border border-slate-200 px-4 py-2 text-xs uppercase tracking-[0.25em] text-slate-600 transition hover:border-sky-400/40 hover:text-sky-600"
                                :class="selectedStorage === value.value ? 'border-sky-400/60 text-sky-600' : ''"
                                @click="selectedStorage = value.value"
                            >
                                {{ value.display_value }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 text-sm text-slate-600">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Key features</h2>
                    <ul class="grid gap-3 sm:grid-cols-2">
                        <li v-for="spec in featureHighlights" :key="spec" class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-2 w-2 rounded-full bg-sky-400"></span>
                            <span>{{ spec }}</span>
                        </li>
                    </ul>
                </div>

                <div class="flex flex-wrap gap-3 text-sm">
                    <button class="rounded-full bg-sky-600 px-6 py-3 font-semibold uppercase tracking-[0.28em] text-white transition hover:bg-sky-500">
                        Add to cart
                    </button>
                    <button class="rounded-full border border-slate-200 px-6 py-3 text-sm uppercase tracking-[0.28em] text-slate-600 transition hover:border-sky-400/40 hover:text-sky-600">
                        Add to compare
                    </button>
                    <button class="rounded-full border border-slate-200 px-6 py-3 text-sm uppercase tracking-[0.28em] text-slate-600 transition hover:border-sky-400/40 hover:text-sky-600">
                        Save to wishlist
                    </button>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Specification snapshot</h2>
                    <div class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 md:grid-cols-2">
                        <div v-for="group in product.specifications" :key="group.label" class="space-y-3 text-sm text-slate-600">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">{{ group.label }}</p>
                            <ul class="space-y-2">
                                <li v-for="item in group.items" :key="item.label">
                                    <span class="text-slate-400">{{ item.label }}:</span>
                                    <span class="text-slate-700"> {{ item.value }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section v-if="relatedProducts.length" class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-slate-800">Recommended accessories</h2>
                <RouterLink :to="'/category/' + route.params.slug" class="text-sm text-sky-600 transition hover:text-sky-700">View all -></RouterLink>
            </div>
            <ProductGrid :products="relatedProducts" dense />
        </section>
    </div>
    <div v-else class="py-24 text-center text-sm uppercase tracking-[0.3em] text-slate-400">Loading product...</div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import ProductGrid from '@/components/product/ProductGrid.vue';
import ProductMediaGallery from '@/components/product/ProductMediaGallery.vue';

const route = useRoute();
const loading = ref(true);
const product = ref(null);
const options = ref([]);
const mediaItems = ref([]);
const relatedProducts = ref([]);
const selectedColor = ref('');
const selectedStorage = ref('');

const fetchProduct = async () => {
    loading.value = true;
    try {
        const slug = route.params.slug?.toString();
        const { data } = await axios.get(`/api/catalog/products/${slug}`);
        product.value = data.product;
        options.value = data.product?.options ?? [];
        mediaItems.value = data.product?.media ?? [];
        relatedProducts.value = data.product?.related_products ?? [];

        const colorOption = options.value.find((option) => option.code === 'color');
        const storageOption = options.value.find((option) => option.code === 'storage');
        selectedColor.value = colorOption?.values?.[0]?.value ?? '';
        selectedStorage.value = storageOption?.values?.[0]?.value ?? '';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProduct);

watch(
    () => route.params.slug,
    () => {
        fetchProduct();
    }
);

const breadcrumbs = computed(() => [
    { label: 'Home', to: '/' },
    { label: 'Laptops & computers', to: '/category/laptops' },
    { label: product.value?.name ?? 'Product', to: route.fullPath },
]);

const featureHighlights = computed(() => {
    const groups = product.value?.specifications ?? [];
    return groups
        .flatMap((group) => group.items)
        .map((item) => `${item.label}: ${item.value}`)
        .slice(0, 6);
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value ?? 0);
</script>

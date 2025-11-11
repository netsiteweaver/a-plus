<template>
    <div v-if="!loading && product" class="space-y-12">
        <Breadcrumbs :items="breadcrumbs" />

        <div class="grid gap-10 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-5">
                <ProductMediaGallery :media-items="mediaItems" />
            </div>

            <section class="space-y-8 lg:col-span-7">
                <header class="space-y-4">
                    <div class="flex items-center gap-3 text-xs uppercase tracking-[0.25em]">
                        <span :class="getBadgeColor(product.badge)" class="font-semibold">{{ product.badge ?? 'New arrival' }}</span>
                        <span class="hidden h-0.5 w-8 bg-slate-300/40 md:block"></span>
                        <span class="text-slate-600">{{ product.brand }}</span>
                    </div>
                    <h1 class="text-3xl font-semibold text-slate-800">{{ product.name }}</h1>
                    <p class="text-sm text-slate-500">{{ product.subtitle }}</p>

                    <!-- Enhanced pricing and trust signals -->
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-baseline gap-4">
                            <span class="text-3xl font-bold text-slate-900">{{ formatCurrency(product.price) }}</span>
                            <span v-if="product.compare_at_price" class="text-xl text-red-500 line-through">{{ formatCurrency(product.compare_at_price) }}</span>
                            <span v-if="product.compare_at_price" class="rounded-full bg-gradient-to-r from-red-500 to-red-600 px-3 py-1 text-sm font-bold text-white shadow-lg">
                                Save {{ formatCurrency(product.compare_at_price - product.price) }}
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <!-- Rating with star -->
                            <span class="inline-flex items-center gap-2">
                                <span class="text-lg text-amber-500">★</span>
                                <span class="font-semibold text-slate-700">{{ product.rating.toFixed(1) }}</span>
                                <span class="text-slate-400">({{ product.rating_count }} reviews)</span>
                            </span>
                            
                            <!-- In stock - green trust signal -->
                            <span class="inline-flex items-center gap-1.5 font-semibold text-green-600">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                                In stock - Ships today
                            </span>
                            
                            <span class="text-slate-400">SKU {{ product.sku }}</span>
                        </div>

                        <!-- Urgency indicator -->
                        <div class="rounded-2xl border-2 border-blue-400 bg-gradient-to-r from-blue-50 to-sky-50 p-3">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2 text-blue-700">
                                    <span class="flex h-2 w-2">
                                        <span class="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-blue-400 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                                    </span>
                                    <span class="text-sm font-semibold">Hot Deal</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-mono font-semibold">{{ timeRemaining }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Free shipping banner -->
                        <!-- <div class="rounded-2xl border-l-4 border-green-600 bg-green-50 p-3">
                            <p class="flex items-center gap-2 text-sm font-semibold text-green-800">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.18 3a65.25 65.25 0 0113.36 1.412.75.75 0 01.58.875 48.645 48.645 0 01-1.618 6.2.75.75 0 01-.712.513H6a2.503 2.503 0 00-2.292 1.5H17.25a.75.75 0 010 1.5H2.76a.75.75 0 01-.748-.807 4.002 4.002 0 012.716-3.486L3.626 2.716a.25.25 0 00-.248-.216H1.75A.75.75 0 011 1.75zM6 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                </svg>
                                Free shipping on orders over $50 · Arrives in 1-2 business days
                            </p>
                        </div> -->
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
                    <!-- Primary CTA - Blue for action/conversion -->
                    <button class="group relative overflow-hidden rounded-full bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-4 font-bold uppercase tracking-[0.28em] text-white shadow-xl shadow-blue-500/30 transition-all hover:from-blue-600 hover:to-blue-700 hover:shadow-2xl hover:shadow-blue-500/40">
                        <span class="relative z-10">Add to cart</span>
                        <div class="absolute inset-0 -z-0 bg-gradient-to-r from-blue-600 to-blue-700 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    </button>
                    
                    <!-- Secondary actions - Subtle with sky accent on hover -->
                    <button class="rounded-full border-2 border-slate-200 px-6 py-3 text-sm uppercase tracking-[0.28em] text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700">
                        Add to compare
                    </button>
                    <button class="rounded-full border-2 border-slate-200 px-6 py-3 text-sm uppercase tracking-[0.28em] text-slate-600 transition hover:border-purple-400 hover:bg-purple-50 hover:text-purple-700">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.653 16.915l-.005-.003-.019-.01a20.759 20.759 0 01-1.162-.682 22.045 22.045 0 01-2.582-1.9C4.045 12.733 2 10.352 2 7.5a4.5 4.5 0 018-2.828A4.5 4.5 0 0118 7.5c0 2.852-2.044 5.233-3.885 6.82a22.049 22.049 0 01-3.744 2.582l-.019.01-.005.003h-.002a.739.739 0 01-.69.001l-.002-.001z" />
                            </svg>
                            Wishlist
                        </span>
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
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import ProductGrid from '@/components/product/ProductGrid.vue';
import ProductMediaGallery from '@/components/product/ProductMediaGallery.vue';
import { useCurrency } from '@/composables/useCurrency';

const route = useRoute();
const { formatCurrency } = useCurrency();
const loading = ref(true);
const product = ref(null);
const options = ref([]);
const mediaItems = ref([]);
const relatedProducts = ref([]);
const selectedColor = ref('');
const selectedStorage = ref('');
const timeRemaining = ref('23h 45m 12s');

// Countdown timer for urgency
let timerInterval = null;
const startCountdown = () => {
    let hours = 23;
    let minutes = 45;
    let seconds = 12;
    
    timerInterval = setInterval(() => {
        seconds--;
        if (seconds < 0) {
            seconds = 59;
            minutes--;
            if (minutes < 0) {
                minutes = 59;
                hours--;
                if (hours < 0) {
                    hours = 23;
                }
            }
        }
        timeRemaining.value = `${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
};

const getBadgeColor = (badge) => {
    const badgeLower = badge?.toLowerCase() || '';
    if (badgeLower.includes('pro') || badgeLower.includes('premium')) {
        return 'text-purple-600';
    }
    if (badgeLower.includes('new')) {
        return 'text-yellow-600';
    }
    if (badgeLower.includes('featured')) {
        return 'text-blue-600';
    }
    return 'text-sky-600';
};

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

onMounted(() => {
    fetchProduct();
    startCountdown();
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

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
</script>

<template>
    <div class="space-y-12">
        <Breadcrumbs :items="breadcrumbs" />

        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <ProductMediaGallery :media="productMedia" />
            </div>

            <div class="space-y-8 lg:col-span-7">
                <header class="space-y-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-emerald-200">Ships in 48h</p>
                    <h1 class="text-3xl font-semibold text-white">VisionBook X16 OLED Creator Edition</h1>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-white/60">
                        <span class="inline-flex items-center gap-2">
                            <span>? {{ product.rating.toFixed(1) }}</span>
                            <span class="text-white/30">({{ product.ratingCount }} reviews)</span>
                        </span>
                        <span class="text-xs uppercase tracking-[0.3em] text-white/30">SKU ? VBX16-OLED</span>
                    </div>
                </header>

                <div class="flex items-baseline gap-4">
                    <span class="text-3xl font-semibold text-white">{{ formatCurrency(product.price) }}</span>
                    <span class="text-sm text-white/30 line-through">{{ formatCurrency(product.compareAtPrice) }}</span>
                    <span class="rounded-full border border-emerald-500/40 px-3 py-1 text-xs uppercase tracking-[0.3em] text-emerald-300">includes pro support</span>
                </div>

                <div class="space-y-6 text-sm text-white/70">
                    <p>
                        Portable creator powerhouse with AI-accelerated editing, HDR OLED 16", and liquid metal cooling. Bundled with Aurora Studio suite and 2TB Nvme scratch disk.
                    </p>

                    <div class="space-y-4">
                        <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-white/40">Choose finish</h2>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-for="option in colorOptions"
                                :key="option.value"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs uppercase tracking-[0.25em] transition"
                                :class="selectedColor === option.value ? 'border-emerald-400/60 text-emerald-200' : 'border-white/10 text-white/60 hover:border-emerald-300/40 hover:text-white'"
                                @click="selectedColor = option.value"
                            >
                                <span class="h-3 w-3 rounded-full" :style="`background:${option.swatch}`"></span>
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-white/40">Storage</h2>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-for="option in storageOptions"
                                :key="option"
                                type="button"
                                class="rounded-full border px-4 py-2 text-xs uppercase tracking-[0.25em] transition"
                                :class="selectedStorage === option ? 'border-emerald-400/60 text-emerald-200' : 'border-white/10 text-white/60 hover:border-emerald-300/40 hover:text-white'"
                                @click="selectedStorage = option"
                            >
                                {{ option }}
                            </button>
                        </div>
                    </div>

                    <ul class="grid gap-3 rounded-3xl border border-white/10 bg-slate-900/70 p-5 text-sm text-white/60">
                        <li class="flex items-center gap-3">
                            <span class="text-emerald-300">?</span>
                            Creator suite license (DaVinci Resolve Studio, Adobe? Creative Cloud 3 months)
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-emerald-300">?</span>
                            3-year concierge warranty ? onsite next-business-day replacement
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-emerald-300">?</span>
                            Includes color calibration + workstation optimization session
                        </li>
                    </ul>

                    <div class="flex flex-wrap gap-4">
                        <button class="flex-1 rounded-full bg-emerald-400 px-6 py-3 text-sm font-semibold uppercase tracking-[0.28em] text-slate-950 transition hover:bg-emerald-300">
                            Add to cart ? {{ formatCurrency(product.price) }}
                        </button>
                        <button class="rounded-full border border-white/10 px-6 py-3 text-sm font-semibold uppercase tracking-[0.28em] text-white/70 transition hover:border-emerald-400/40 hover:text-white">
                            Add to compare
                        </button>
                    </div>
                </div>

                <section class="space-y-4">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.25em] text-white/40">Specification preview</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div v-for="group in productSpecifications" :key="group.label" class="space-y-2 rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                            <p class="text-xs uppercase tracking-[0.25em] text-emerald-200">{{ group.label }}</p>
                            <ul class="space-y-1 text-sm text-white/70">
                                <li v-for="item in group.items" :key="item[0]" class="flex justify-between">
                                    <span class="text-white/40">{{ item[0] }}</span>
                                    <span class="text-right text-white/70">{{ item[1] }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <RouterLink to="#" class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.3em] text-emerald-300 transition hover:text-emerald-200">
                        View full spec sheet
                        <span>?</span>
                    </RouterLink>
                </section>
            </div>
        </div>

        <section class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Recommended accessories</h2>
                <RouterLink to="/category/accessories" class="text-sm text-emerald-300 transition hover:text-emerald-200">View all ?</RouterLink>
            </div>
            <ProductGrid :products="relatedProducts" dense />
        </section>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import ProductGrid from '@/components/product/ProductGrid.vue';
import ProductMediaGallery from '@/components/product/ProductMediaGallery.vue';
import { featuredProducts, productMedia, productSpecifications } from '@/data/mockCatalog';

const route = useRoute();

const fallbackProduct = featuredProducts[0];

const product = computed(() => {
    const slug = route.params.slug?.toString();
    if (!slug) return fallbackProduct;
    return featuredProducts.find((item) => item.slug === slug) ?? fallbackProduct;
});

const relatedProducts = computed(() => featuredProducts.filter((item) => item.slug !== product.value.slug).slice(0, 3));

const colorOptions = [
    { label: 'Obsidian black', value: 'obsidian', swatch: '#101820' },
    { label: 'Titanium silver', value: 'titanium', swatch: '#94989E' },
    { label: 'Glacier white', value: 'glacier', swatch: '#f1f5f9' },
];

const storageOptions = ['1TB NVMe', '2TB NVMe', '4TB NVMe'];

const selectedColor = ref(colorOptions[0].value);
const selectedStorage = ref(storageOptions[1]);

const breadcrumbs = computed(() => [
    { label: 'Home', to: '/' },
    { label: 'Laptops & computers', to: '/category/laptops' },
    { label: product.value.name, to: route.fullPath },
]);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);

defineExpose({ selectedColor, selectedStorage });
</script>

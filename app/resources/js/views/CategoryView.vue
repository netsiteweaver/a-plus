<template>
    <div class="space-y-10">
        <Breadcrumbs :items="breadcrumbs" />

        <header class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold text-white capitalize">{{ categoryName }}</h1>
                    <p class="max-w-2xl text-sm text-white/60">
                        Powerhouse creator laptops optimized for 8K RED workflows, Unreal Engine, and AI-assisted color. Tuned thermals, calibrated displays, and workstation-grade reliability.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-white/40">
                    <span>{{ categoryProducts.length }} products</span>
                    <span class="hidden h-0.5 w-8 bg-white/10 md:block"></span>
                    <button class="rounded-full border border-white/10 px-4 py-1.5 text-xs text-white/70 transition hover:border-emerald-400/40 hover:text-white">
                        Export specs
                    </button>
                </div>
            </div>
        </header>

        <div class="grid gap-10 lg:grid-cols-12">
            <aside class="space-y-6 rounded-3xl border border-white/10 bg-slate-900/70 p-6 lg:col-span-3">
                <div class="flex items-center justify-between text-sm text-white/60">
                    <span>Filters</span>
                    <button class="text-xs uppercase tracking-[0.25em] text-emerald-300 transition hover:text-emerald-200">Reset</button>
                </div>
                <div class="space-y-6 text-sm text-white/70">
                    <details v-for="filter in categoryFilters" :key="filter.key" open class="rounded-2xl border border-white/10 bg-slate-900/80">
                        <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-xs font-semibold uppercase tracking-[0.25em] text-white/40">
                            {{ filter.label }}
                            <span class="text-white/20">?</span>
                        </summary>
                        <div class="space-y-3 border-t border-white/5 px-4 py-4">
                            <label
                                v-for="option in filter.options"
                                :key="option.value"
                                class="flex items-center justify-between gap-4 text-xs text-white/60"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-900/60" />
                                    {{ option.label }}
                                </span>
                                <span class="text-white/30">{{ option.count }}</span>
                            </label>
                        </div>
                    </details>
                </div>
            </aside>

            <section class="space-y-6 lg:col-span-9">
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-white/10 bg-slate-900/70 px-5 py-4 text-sm text-white/60">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-xs uppercase tracking-[0.28em] text-white/30">Sort by</span>
                        <button class="rounded-full border border-white/10 px-3 py-1 text-xs text-white/70 transition hover:border-emerald-400/40 hover:text-emerald-200">
                            Top rated
                        </button>
                        <button class="rounded-full border border-white/10 px-3 py-1 text-xs text-white/70 transition hover:border-emerald-400/40 hover:text-emerald-200">
                            Newest
                        </button>
                        <button class="rounded-full border border-white/10 px-3 py-1 text-xs text-white/70 transition hover:border-emerald-400/40 hover:text-emerald-200">
                            Price ? Low to high
                        </button>
                    </div>
                    <span class="text-xs uppercase tracking-[0.25em] text-white/40">Compare (0)</span>
                </div>

                <ProductGrid :products="categoryProducts" dense />
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import ProductGrid from '@/components/product/ProductGrid.vue';
import { categoryFilters, categoryProducts } from '@/data/mockCatalog';

const route = useRoute();

const categoryName = computed(() => route.params.slug?.toString().replace(/-/g, ' ') ?? 'Category');

const breadcrumbs = computed(() => [
    { label: 'Home', to: '/' },
    { label: 'Catalog', to: '/category/laptops' },
    { label: categoryName.value, to: route.fullPath },
]);
</script>

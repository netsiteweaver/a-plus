<template>
    <div class="space-y-20">
        <section class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-slate-950 p-10 shadow-[0_25px_60px_-35px_rgba(16,185,129,0.6)] lg:p-16">
            <div class="grid gap-12 lg:grid-cols-2">
                <div class="space-y-6 text-white">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200">Drop of the week</p>
                    <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">Creator Studio Event ? Save up to 25%</h1>
                    <p class="text-white/70">
                        Curated workstations for 8K editing, real-time ray tracing, and color-accurate finishing. Bundle includes studio monitor, calibrated lighting, and concierge setup.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-white/60">
                        <span class="rounded-full border border-white/10 px-4 py-1 uppercase tracking-[0.3em] text-emerald-200">RTX? 4090 rigs</span>
                        <span class="rounded-full border border-white/10 px-4 py-1 uppercase tracking-[0.3em] text-white/40">Ships in 48h</span>
                    </div>
                    <div class="flex items-center gap-5 pt-4 text-sm">
                        <RouterLink
                            to="/category/creator-studio"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-400 px-6 py-3 font-semibold uppercase tracking-[0.28em] text-slate-950 transition hover:bg-emerald-300"
                        >
                            Build yours
                            <span>?</span>
                        </RouterLink>
                        <RouterLink to="/services/installation" class="inline-flex items-center gap-2 text-white/70 transition hover:text-white">
                            Concierge install
                            <span class="text-xs">?</span>
                        </RouterLink>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 rounded-full bg-emerald-500/10 blur-3xl"></div>
                    <img
                        src="https://images.unsplash.com/photo-1580894908357-0819643a1d84?auto=format&fit=crop&w=1200&q=80"
                        alt="Creator studio setup"
                        class="relative z-10 w-full rounded-[2rem] object-cover shadow-2xl"
                        loading="lazy"
                    />
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-white/40">Lightning deals</p>
                    <h2 class="text-2xl font-semibold text-white">Daily price drops</h2>
                </div>
                <RouterLink to="/category/deals" class="text-sm text-emerald-300 transition hover:text-emerald-200">View all deals ?</RouterLink>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <article
                    v-for="deal in dailyDeals"
                    :key="deal.id"
                    class="group flex flex-col justify-between rounded-3xl border border-white/10 bg-slate-900/70 p-6 transition hover:border-emerald-400/40"
                >
                    <div class="flex items-start justify-between gap-6">
                        <div class="space-y-2">
                            <p class="text-xs uppercase tracking-[0.28em] text-emerald-200">Ends {{ deal.endsIn }}</p>
                            <RouterLink :to="`/product/${deal.slug}`" class="text-lg font-semibold text-white transition group-hover:text-emerald-200">
                                {{ deal.name }}
                            </RouterLink>
                            <ul class="text-xs text-white/50">
                                <li v-for="meta in deal.meta" :key="meta">{{ meta }}</li>
                            </ul>
                        </div>
                        <img :src="deal.image" :alt="deal.name" class="h-28 w-28 rounded-2xl object-cover" loading="lazy" />
                    </div>
                    <div class="mt-4 flex items-baseline gap-3">
                        <span class="text-xl font-semibold text-white">{{ formatCurrency(deal.price) }}</span>
                        <span class="text-sm text-white/30 line-through">{{ formatCurrency(deal.compareAtPrice) }}</span>
                    </div>
                </article>
            </div>
        </section>

        <section class="space-y-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-white/40">Shop by need</p>
                    <h2 class="text-2xl font-semibold text-white">Featured categories</h2>
                </div>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <RouterLink
                    v-for="category in featuredCategories"
                    :key="category.to"
                    :to="category.to"
                    class="group overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/60 shadow-xl transition hover:border-emerald-400/40"
                >
                    <div class="relative pb-[70%]">
                        <img :src="category.image" :alt="category.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                        <span class="absolute left-5 top-5 rounded-full border border-white/10 bg-emerald-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200">{{ category.accent }}</span>
                    </div>
                    <div class="space-y-3 p-6">
                        <h3 class="text-lg font-semibold text-white">{{ category.name }}</h3>
                        <p class="text-sm text-white/60">{{ category.description }}</p>
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-300">
                            Explore
                            <span>?</span>
                        </span>
                    </div>
                </RouterLink>
            </div>
        </section>

        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-white/40">Trending now</p>
                    <h2 class="text-2xl font-semibold text-white">Most-loved devices</h2>
                </div>
                <RouterLink to="/category/most-loved" class="text-sm text-emerald-300 transition hover:text-emerald-200">See curated list ?</RouterLink>
            </div>
            <ProductGrid :products="featuredProducts" />
        </section>
    </div>
</template>

<script setup>
import ProductGrid from '@/components/product/ProductGrid.vue';
import { dailyDeals, featuredCategories, featuredProducts } from '@/data/mockCatalog';

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
</script>

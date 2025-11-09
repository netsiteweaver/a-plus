<template>
    <div v-if="!loading" class="space-y-20">
        <section
            v-if="hero"
            class="relative overflow-hidden rounded-[2.5rem] border-2 border-orange-200 bg-gradient-to-br from-orange-50 via-amber-50 to-white p-10 shadow-[0_25px_60px_-35px_rgba(249,115,22,0.5)] lg:p-16"
        >
            <!-- Animated gradient background -->
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-200/20 via-transparent to-purple-200/20"></div>
            
            <div class="relative z-10 grid gap-12 lg:grid-cols-2">
                <div class="space-y-6 text-slate-800">
                    <div class="flex items-center gap-3">
                        <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.28em] text-orange-600">
                            <span class="flex h-2 w-2">
                                <span class="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-orange-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-orange-500"></span>
                            </span>
                            {{ hero.badge ?? 'Featured drop' }}
                        </p>
                    </div>
                    <h1 class="bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-4xl font-bold tracking-tight text-transparent sm:text-5xl">{{ hero.title }}</h1>
                    <p class="text-lg text-slate-700">{{ hero.description }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                        <span class="rounded-full border border-purple-300 bg-gradient-to-r from-purple-100 to-purple-50 px-4 py-1.5 font-semibold uppercase tracking-[0.3em] text-purple-700 shadow-sm">Pro creator bundles</span>
                        <span class="rounded-full border border-green-300 bg-gradient-to-r from-green-100 to-green-50 px-4 py-1.5 font-semibold uppercase tracking-[0.3em] text-green-700 shadow-sm">Next-day delivery</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 pt-4 text-sm">
                        <RouterLink
                            :to="hero.cta?.to ?? '/'"
                            class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-4 font-bold uppercase tracking-[0.28em] text-white shadow-2xl shadow-orange-500/40 transition-all hover:from-orange-600 hover:to-orange-700 hover:shadow-2xl hover:shadow-orange-500/60"
                        >
                            <span class="relative z-10">{{ hero.cta?.label ?? 'Shop now' }}</span>
                            <svg class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </RouterLink>
                        <RouterLink to="/services/installation" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-300 bg-white/80 px-6 py-3 font-semibold text-slate-700 backdrop-blur-sm transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700">
                            Concierge install
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </RouterLink>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-orange-300/40 to-purple-300/40 blur-3xl"></div>
                    <img :src="hero.image" :alt="hero.title" class="relative z-10 w-full rounded-[2rem] object-cover shadow-2xl ring-2 ring-white/50" loading="lazy" />
                </div>
            </div>
        </section>

        <section v-if="dailyDeals.length" class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.3em] text-red-600">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M13.2 2.24a.75.75 0 00.04 1.06l2.1 1.95H6.75a.75.75 0 000 1.5h8.59l-2.1 1.95a.75.75 0 101.02 1.1l3.5-3.25a.75.75 0 000-1.1l-3.5-3.25a.75.75 0 00-1.06.04zm-6.4 8a.75.75 0 00-1.06-.04l-3.5 3.25a.75.75 0 000 1.1l3.5 3.25a.75.75 0 101.02-1.1l-2.1-1.95h8.59a.75.75 0 000-1.5H4.66l2.1-1.95a.75.75 0 00.04-1.06z" clip-rule="evenodd" />
                        </svg>
                        Lightning deals
                    </p>
                    <h2 class="text-2xl font-bold text-slate-800">Daily price drops</h2>
                </div>
                <RouterLink to="/category/deals" class="inline-flex items-center gap-2 rounded-full border-2 border-orange-400 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 transition hover:bg-orange-100">
                    View all deals
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </RouterLink>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <article
                    v-for="deal in dailyDeals"
                    :key="deal.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border-2 border-red-200 bg-gradient-to-br from-red-50 via-orange-50 to-white p-6 shadow-lg shadow-red-500/10 transition hover:border-red-400 hover:shadow-2xl hover:shadow-red-500/20"
                >
                    <!-- Urgency badge -->
                    <div class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-red-500 to-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-lg">
                        <span v-if="deal.compare_at_price">-{{ Math.round(((deal.compare_at_price - deal.price) / deal.compare_at_price) * 100) }}%</span>
                        <span v-else>HOT</span>
                    </div>
                    
                    <div class="flex items-start justify-between gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-1.5 w-1.5 animate-ping rounded-full bg-orange-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-700">Limited time</p>
                            </div>
                            <RouterLink :to="`/product/${deal.slug}`" class="block text-lg font-bold text-slate-900 transition group-hover:text-orange-600">
                                {{ deal.name }}
                            </RouterLink>
                            <ul class="space-y-1 text-xs text-slate-600">
                                <li v-for="meta in deal.meta" :key="meta" class="flex items-start gap-1.5">
                                    <span class="mt-1 inline-block h-1 w-1 rounded-full bg-orange-400"></span>
                                    <span>{{ meta }}</span>
                                </li>
                            </ul>
                        </div>
                        <img :src="deal.image" :alt="deal.name" class="h-28 w-28 rounded-2xl object-cover shadow-lg ring-2 ring-white" loading="lazy" />
                    </div>
                    <div class="mt-5 flex items-center justify-between border-t border-orange-200 pt-4">
                        <div class="flex items-baseline gap-3">
                            <span class="text-2xl font-bold text-slate-900">{{ formatCurrency(deal.price) }}</span>
                            <span v-if="deal.compare_at_price" class="text-base text-red-500 line-through">{{ formatCurrency(deal.compare_at_price) }}</span>
                        </div>
                        <button class="rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 text-xs font-bold uppercase tracking-[0.28em] text-white shadow-lg shadow-orange-500/30 transition-all hover:from-orange-600 hover:to-orange-700 hover:shadow-xl">
                            Grab it
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <section v-if="featuredCategories.length" class="space-y-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-purple-600">Shop by need</p>
                    <h2 class="text-2xl font-bold text-slate-800">Featured categories</h2>
                </div>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <RouterLink
                    v-for="category in featuredCategories"
                    :key="category.slug"
                    :to="`/category/${category.slug}`"
                    class="group overflow-hidden rounded-[2rem] border-2 border-slate-200 bg-white shadow-xl transition hover:border-purple-400 hover:shadow-2xl hover:shadow-purple-500/20"
                >
                    <div class="relative pb-[70%]">
                        <img 
                            v-if="category.image" 
                            :src="category.image" 
                            :alt="category.name" 
                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-110" 
                            loading="lazy"
                            @error="(e) => e.target.style.display = 'none'"
                        />
                        <div 
                            v-else 
                            class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-purple-100 to-purple-50"
                        >
                            <svg class="h-16 w-16 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <span v-if="category.accent" class="absolute left-5 top-5 rounded-full border-2 border-white/80 bg-white/90 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.3em] text-purple-700 shadow-lg backdrop-blur-sm">{{ category.accent }}</span>
                    </div>
                    <div class="space-y-3 p-6">
                        <h3 class="text-lg font-bold text-slate-900 transition group-hover:text-purple-700">{{ category.name }}</h3>
                        <p class="text-sm text-slate-600">{{ category.description }}</p>
                        <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.3em] text-purple-600 transition group-hover:gap-3">
                            Explore
                            <svg class="h-3 w-3 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </RouterLink>
            </div>
        </section>

        <section v-if="featuredProducts.length" class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Trending now</p>
                    <h2 class="text-2xl font-semibold text-slate-800">Most-loved devices</h2>
                </div>
                <RouterLink to="/category/most-loved" class="text-sm text-sky-600 transition hover:text-sky-700">See curated list -></RouterLink>
            </div>
            <ProductGrid :products="featuredProducts" />
        </section>
    </div>
    <div v-else class="py-24 text-center text-sm uppercase tracking-[0.3em] text-slate-400">Loading catalog...</div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import ProductGrid from '@/components/product/ProductGrid.vue';

const loading = ref(true);
const hero = ref(null);
const featuredCategories = ref([]);
const dailyDeals = ref([]);
const featuredProducts = ref([]);

async function fetchHome() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/catalog/home');
        hero.value = data.hero;
        featuredCategories.value = data.featured_categories ?? [];
        dailyDeals.value = data.daily_deals ?? [];
        featuredProducts.value = data.featured_products ?? [];
    } finally {
        loading.value = false;
    }
}

onMounted(fetchHome);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value ?? 0);
</script>

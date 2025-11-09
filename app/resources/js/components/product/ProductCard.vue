<template>
    <RouterLink
        :to="`/product/${product.slug}`"
        class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:border-orange-400/60 hover:shadow-[0_12px_30px_-12px_rgba(249,115,22,0.45)]"
    >
        <div class="relative pb-[70%]">
            <img
                :src="product.image"
                :alt="product.name"
                class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <!-- Enhanced badge with better color psychology -->
            <span
                v-if="product.badge"
                :class="getBadgeClasses(product.badge)"
                class="absolute left-4 top-4 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] shadow-lg"
            >
                {{ product.badge }}
            </span>
            <!-- Discount percentage indicator -->
            <span
                v-if="product.compare_at_price && product.price"
                class="absolute right-4 top-4 rounded-full bg-gradient-to-br from-red-500 to-red-600 px-3 py-1 text-xs font-bold text-white shadow-lg"
            >
                -{{ Math.round(((product.compare_at_price - product.price) / product.compare_at_price) * 100) }}%
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex items-start justify-between gap-4 text-sm text-slate-500">
                <!-- Green trust signal for in-stock -->
                <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.25em] text-green-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    in stock
                </p>
                <!-- Enhanced rating display -->
                <div class="flex items-center gap-1 text-xs">
                    <span class="text-amber-500">★</span>
                    <span class="font-semibold text-slate-700">{{ Number(product.rating ?? 0).toFixed(1) }}</span>
                    <span class="text-slate-400">({{ product.rating_count ?? 0 }})</span>
                </div>
            </div>

            <p class="text-base font-semibold text-slate-800 transition group-hover:text-orange-600">{{ product.name }}</p>

            <ul class="space-y-1 text-xs text-slate-500">
                <li v-for="meta in product.meta" :key="meta">{{ meta }}</li>
            </ul>

            <div class="mt-auto space-y-2 pt-2">
                <div class="flex items-baseline gap-3">
                    <span class="text-lg font-bold text-slate-900">{{ formatCurrency(product.price) }}</span>
                    <span v-if="product.compare_at_price" class="text-sm text-red-500 line-through">{{ formatCurrency(product.compare_at_price) }}</span>
                </div>
                <!-- Quick add to cart button -->
                <button 
                    @click.prevent="handleQuickAdd"
                    class="w-full rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.28em] text-white opacity-0 shadow-lg shadow-orange-500/30 transition-all hover:from-orange-600 hover:to-orange-700 hover:shadow-xl hover:shadow-orange-500/40 group-hover:opacity-100"
                >
                    Quick add
                </button>
            </div>
        </div>
    </RouterLink>
</template>

<script setup>
const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(value ?? 0);

const getBadgeClasses = (badge) => {
    const badgeLower = badge?.toLowerCase() || '';
    
    // Premium/Pro products - Purple/Gold
    if (badgeLower.includes('pro') || badgeLower.includes('premium')) {
        return 'border-purple-200 bg-gradient-to-br from-purple-100 to-purple-50 text-purple-700';
    }
    
    // New arrivals - Yellow/Energy
    if (badgeLower.includes('new')) {
        return 'border-yellow-200 bg-gradient-to-br from-yellow-100 to-yellow-50 text-yellow-700';
    }
    
    // Featured/Popular - Orange
    if (badgeLower.includes('featured') || badgeLower.includes('popular')) {
        return 'border-orange-200 bg-gradient-to-br from-orange-100 to-orange-50 text-orange-700';
    }
    
    // Default - Sky blue
    return 'border-sky-200 bg-gradient-to-br from-sky-100 to-sky-50 text-sky-700';
};

const handleQuickAdd = (event) => {
    event.preventDefault();
    // TODO: Implement quick add to cart functionality
    console.log('Quick add:', props.product.slug);
};
</script>

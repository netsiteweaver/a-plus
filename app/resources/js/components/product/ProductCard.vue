<template>
    <RouterLink
        :to="`/product/${product.slug}`"
        class="group flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-900/70 transition hover:border-emerald-400/40 hover:shadow-[0_0_25px_-10px_rgba(16,185,129,0.6)]"
    >
        <div class="relative pb-[70%]">
            <img
                :src="product.image"
                :alt="product.name"
                class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <span
                v-if="product.badge"
                class="absolute left-4 top-4 rounded-full border border-white/10 bg-emerald-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200"
            >
                {{ product.badge }}
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex items-start justify-between gap-4 text-sm text-white/60">
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-200/70">in stock</p>
                <div class="flex items-center gap-1 text-xs text-white/40">
                    <span>?</span>
                    <span>{{ product.rating.toFixed(1) }}</span>
                    <span class="text-white/30">({{ product.ratingCount }})</span>
                </div>
            </div>

            <p class="text-base font-semibold text-white">{{ product.name }}</p>

            <ul class="space-y-1 text-xs text-white/50">
                <li v-for="meta in product.meta" :key="meta">{{ meta }}</li>
            </ul>

            <div class="mt-auto flex items-baseline gap-3 pt-2">
                <span class="text-lg font-semibold text-white">{{ formatCurrency(product.price) }}</span>
                <span v-if="product.compareAtPrice" class="text-sm text-white/30 line-through">{{ formatCurrency(product.compareAtPrice) }}</span>
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
    }).format(value);
</script>

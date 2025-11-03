<template>
    <RouterLink
        :to="`/product/${product.slug}`"
        class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:border-blue-400/60 hover:shadow-xl"
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
                class="absolute left-4 top-4 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-blue-600"
            >
                {{ product.badge }}
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex items-start justify-between gap-4 text-sm text-slate-500">
                <p class="text-xs uppercase tracking-[0.25em] text-blue-600/80">in stock</p>
                <div class="flex items-center gap-1 text-xs text-slate-400">
                    <span>*</span>
                    <span>{{ Number(product.rating ?? 0).toFixed(1) }}</span>
                    <span class="text-slate-300">({{ product.rating_count ?? 0 }})</span>
                </div>
            </div>

            <p class="text-base font-semibold text-slate-900">{{ product.name }}</p>

            <ul class="space-y-1 text-xs text-slate-500">
                <li v-for="meta in product.meta" :key="meta">{{ meta }}</li>
            </ul>

            <div class="mt-auto flex items-baseline gap-3 pt-2">
                <span class="text-lg font-semibold text-slate-900">{{ formatCurrency(product.price) }}</span>
                <span v-if="product.compare_at_price" class="text-sm text-slate-400 line-through">{{ formatCurrency(product.compare_at_price) }}</span>
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
</script>

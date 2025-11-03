<template>
    <div :class="gridClass">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
        default: () => [],
    },
    dense: {
        type: Boolean,
        default: false,
    },
});

const gridClass = computed(() =>
    props.dense
        ? 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3'
        : 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'
);
</script>

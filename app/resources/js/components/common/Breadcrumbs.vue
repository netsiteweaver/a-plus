<template>
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-slate-400">
        <RouterLink
            v-for="(crumb, index) in items"
            :key="crumb.to ?? crumb.label"
            :to="crumb.to ?? '#'"
            class="inline-flex items-center gap-2 transition hover:text-blue-600"
        >
            <span>{{ crumb.label }}</span>
            <span v-if="index < items.length - 1" class="text-slate-300">/</span>
        </RouterLink>
    </nav>
</template>

<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
        default: () => [],
    },
});
</script>

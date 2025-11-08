<template>
    <component :is="layoutComponent">
        <router-view />
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/components/layout/AppLayout.vue';
import AdminLayout from '@/components/admin/AdminLayout.vue';
import AdminAuthLayout from '@/components/admin/AdminAuthLayout.vue';

const route = useRoute();

const layoutMap = {
    default: AppLayout,
    admin: AdminLayout,
    auth: AdminAuthLayout,
};

const layoutComponent = computed(() => {
    const layoutKey = route.meta?.layout ?? 'default';
    return layoutMap[layoutKey] ?? AppLayout;
});
</script>

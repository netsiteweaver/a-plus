<template>
    <aside
        class="hidden border-r border-slate-200 bg-white transition-all duration-200 lg:block"
        :class="collapsed ? 'w-20' : 'w-72'"
    >
        <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                <span class="text-lg font-semibold">A+</span>
            </div>
            <div v-if="!collapsed" class="flex flex-col">
                <span class="text-sm font-medium text-slate-500">Backoffice</span>
                <span class="text-lg font-semibold text-slate-900">A+ Commerce</span>
            </div>
        </div>

        <nav class="space-y-2 px-3 py-4">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 lg:hidden"
                @click="$emit('toggle', !collapsed)"
            >
                <span>Toggle Menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </button>

            <div v-for="section in sections" :key="section.title" class="space-y-1">
                <p v-if="!collapsed" class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    {{ section.title }}
                </p>

                <router-link
                    v-for="item in section.items"
                    :key="item.name"
                    :to="item.to"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition"
                    :class="isActive(item.to) ? 'bg-sky-50 text-sky-700' : 'hover:bg-slate-100 hover:text-slate-900'"
                >
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:bg-sky-100 group-hover:text-sky-600">
                        <component :is="resolveIcon(item.icon)" class="h-5 w-5" />
                    </span>
                    <span v-if="!collapsed">{{ item.label }}</span>
                </router-link>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue';
import { useRoute } from 'vue-router';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const route = useRoute();

const sections = computed(() => [
    {
        title: 'Overview',
        items: [
            {
                name: 'dashboard',
                label: 'Dashboard',
                to: { name: 'admin.dashboard' },
                icon: 'dashboard',
            },
        ],
    },
    {
        title: 'Catalog',
        items: [
            {
                name: 'products',
                label: 'Products',
                to: { name: 'admin.products.index' },
                icon: 'cube',
            },
            {
                name: 'categories',
                label: 'Categories',
                to: { name: 'admin.categories.index' },
                icon: 'folder',
            },
            {
                name: 'brands',
                label: 'Brands',
                to: { name: 'admin.brands.index' },
                icon: 'sparkles',
            },
            {
                name: 'attributes',
                label: 'Attributes',
                to: { name: 'admin.attributes.index' },
                icon: 'adjustments',
            },
        ],
    },
]);

function isActive(to) {
    if (!to?.name) {
        return false;
    }

    return route.name === to.name || route.path.startsWith(to.path ?? '');
}

const iconComponents = {
    dashboard: defineComponent({
        name: 'DashboardIcon',
        render() {
            return h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': 1.5,
                },
                [
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'M3 9h7V3H3v6Zm11 12h7v-6h-7v6Zm0-9h7V3h-7v9ZM3 21h7v-9H3v9Z',
                    }),
                ]
            );
        },
    }),
    cube: defineComponent({
        name: 'CubeIcon',
        render() {
            return h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': 1.5,
                },
                [
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'm20.25 7.5-8.25 4.5-8.25-4.5m16.5 0L12 3m8.25 4.5v9l-8.25 4.5m0-9L3.75 7.5m8.25 4.5v9m0-18-8.25 4.5v9l8.25 4.5',
                    }),
                ]
            );
        },
    }),
    folder: defineComponent({
        name: 'FolderIcon',
        render() {
            return h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': 1.5,
                },
                [
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'M3 7.5a2.25 2.25 0 0 1 2.25-2.25h4.318a2.25 2.25 0 0 1 1.591.659l1.341 1.341a2.25 2.25 0 0 0 1.591.659H18.75A2.25 2.25 0 0 1 21 10.5v6.75A2.25 2.25 0 0 1 18.75 19.5H5.25A2.25 2.25 0 0 1 3 17.25v-9.75Z',
                    }),
                ]
            );
        },
    }),
    sparkles: defineComponent({
        name: 'SparklesIcon',
        render() {
            return h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': 1.5,
                },
                [
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'M9.813 15.904 9 18.75l-.813-2.846a3 3 0 0 0-2.091-2.091L3.25 13l2.846-.813a3 3 0 0 0 2.091-2.091L9 7.25l.813 2.846a3 3 0 0 0 2.091 2.091l2.846.813-2.846.813a3 3 0 0 0-2.091 2.091ZM18 5.25l.469 1.641a2 2 0 0 0 1.39 1.39L21.5 8.75l-1.641.469a2 2 0 0 0-1.39 1.39L18 12.25l-.469-1.641a2 2 0 0 0-1.39-1.39L14.5 8.75l1.641-.469a2 2 0 0 0 1.39-1.39L18 5.25Z',
                    }),
                ]
            );
        },
    }),
    adjustments: defineComponent({
        name: 'AdjustmentsIcon',
        render() {
            return h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': 1.5,
                },
                [
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'M10.5 6h9M4.5 6h3m3 12h9m-13.5 0h3m6-6h6m-13.5 0h6',
                    }),
                ]
            );
        },
    }),
});

function resolveIcon(name) {
    return iconComponents[name] ?? iconComponents.dashboard;
}
</script>

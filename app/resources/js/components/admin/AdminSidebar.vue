<template>
    <!-- Mobile overlay backdrop -->
    <transition name="fade">
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
            @click="closeMobileMenu"
        ></div>
    </transition>

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white transition-transform duration-300 lg:static lg:z-auto lg:transition-all"
        :class="{
            'translate-x-0': isMobileMenuOpen,
            '-translate-x-full lg:translate-x-0': !isMobileMenuOpen,
            'lg:w-20': collapsed,
            'lg:w-72': !collapsed
        }"
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
                @click="closeMobileMenu"
            >
                <span>Close Menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
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
                    @click="closeMobileMenu"
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

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
    isMobileMenuOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle', 'close-mobile-menu']);

const route = useRoute();

function closeMobileMenu() {
    emit('close-mobile-menu');
}

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
    {
        title: 'System',
        items: [
            {
                name: 'settings',
                label: 'Settings',
                to: { name: 'admin.settings' },
                icon: 'cog',
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
    cog: defineComponent({
        name: 'CogIcon',
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
                        d: 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z',
                    }),
                    h('path', {
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        d: 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    }),
                ]
            );
        },
    }),
};

function resolveIcon(name) {
    return iconComponents[name] ?? iconComponents.dashboard;
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

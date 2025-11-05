<template>
    <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur-sm lg:px-8">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 lg:hidden"
                @click="$emit('toggle-sidebar', !collapsed)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
                </svg>
            </button>

            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">A+ Backoffice</span>
                <span class="text-lg font-semibold text-slate-900">{{ currentTitle }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-500 shadow-sm lg:flex">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A6.75 6.75 0 1 0 6.75 6.75a6.75 6.75 0 0 0 9.9 9.9Z" />
                </svg>
                <span>Command palette</span>
                <kbd class="rounded border border-slate-200 bg-slate-50 px-1 text-xs text-slate-400">⌘K</kbd>
            </div>

            <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-600 shadow-sm">
                <span class="hidden font-medium lg:inline">{{ auth.displayName }}</span>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-100 text-sky-600"
                    @click="handleLogout"
                    title="Sign out"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const route = useRoute();
const auth = useAuthStore();

const currentTitle = computed(() => route.meta?.title ?? 'Overview');

async function handleLogout() {
    await auth.logout();
    window.location.href = '/admin/login';
}
</script>

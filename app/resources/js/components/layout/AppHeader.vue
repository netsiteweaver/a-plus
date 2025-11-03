<template>
    <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-900/80 backdrop-blur-xl">
        <div class="hidden border-b border-white/5 bg-slate-900/70 text-xs text-white/60 md:block">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-2">
                <p class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                    Mega Electronics - premium tech, next-day delivery in select cities.
                </p>
                <nav class="flex items-center gap-5">
                    <RouterLink
                        v-for="item in utilityNavigation"
                        :key="item.to"
                        :to="item.to"
                        class="transition hover:text-white"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>
            </div>
        </div>

        <div class="mx-auto flex max-w-6xl items-center gap-4 px-6 py-4">
            <RouterLink to="/" class="flex items-center gap-3 text-lg font-semibold tracking-tight text-white">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                    ME
                </span>
                <span class="hidden sm:inline">Mega Electronics</span>
            </RouterLink>

            <div class="relative hidden flex-1 lg:block">
                <input
                    type="search"
                    placeholder="Search laptops, wearables, smart home..."
                    class="w-full rounded-full border border-white/10 bg-slate-900/70 px-5 py-2.5 text-sm text-white/80 placeholder:text-white/40 focus:border-emerald-400/60 focus:outline-none focus:ring-2 focus:ring-emerald-400/40"
                />
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs uppercase tracking-[0.2em] text-white/30">
                    CTRL+K
                </span>
            </div>

            <div class="ml-auto flex items-center gap-3 text-white/70">
                <button class="hidden rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-300 hover:border-emerald-300/60 hover:text-emerald-200 lg:inline-flex">
                    member perks
                </button>
                <RouterLink to="/support" class="hidden text-sm transition hover:text-white md:block">Help</RouterLink>
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 transition hover:border-emerald-400/40 hover:text-white" @click="toggleCartDrawer">
                    <span class="text-xs font-semibold uppercase tracking-[0.3em]">Cart</span>
                </button>
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 transition hover:border-emerald-400/40 hover:text-white" @click="toggleMobileMenu" aria-label="Toggle navigation">
                    <span class="text-sm font-semibold uppercase tracking-[0.3em] md:hidden">Menu</span>
                </button>
            </div>
        </div>

        <div class="relative hidden border-t border-white/5 md:block">
            <nav class="mx-auto flex max-w-6xl items-center px-6">
                <RouterLink
                    v-for="item in primaryNavigation"
                    :key="item.label"
                    :to="item.to"
                    class="relative flex items-center gap-2 px-4 py-3 text-sm font-medium text-white/70 transition hover:text-white"
                    @mouseenter="handleMegaEnter(item)"
                    @focusin="handleMegaEnter(item)"
                    @mouseleave="handleMegaLeave"
                >
                    <span>{{ item.label }}</span>
                    <span v-if="item.columns" class="text-xs text-white/40">v</span>
                </RouterLink>
                <div class="flex-1"></div>
                <RouterLink to="/category/deals" class="inline-flex items-center gap-2 rounded-full border border-emerald-500/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-300 transition hover:border-emerald-300 hover:text-emerald-200">
                    Deals live now
                </RouterLink>
            </nav>

            <MegaMenu :section="activeMegaSection" @close="closeMegaMenu" @mouseleave.native="closeMegaMenu" />
        </div>

        <!-- Mobile menu overlay -->
        <transition name="fade">
            <div
                v-if="ui.isMobileMenuOpen"
                class="fixed inset-0 z-50 flex flex-col bg-slate-950/95 px-6 py-8 text-lg text-white"
            >
                <div class="mb-6 flex items-center justify-between">
                    <span class="text-sm uppercase tracking-[0.28em] text-white/40">Browse</span>
                    <button class="rounded-full border border-white/20 px-3 py-1 text-sm text-white/80" @click="toggleMobileMenu(false)">
                        Close
                    </button>
                </div>
                <nav class="space-y-4">
                    <RouterLink
                        v-for="item in primaryNavigation"
                        :key="item.label"
                        :to="item.to"
                        class="block text-white/80 transition hover:text-white"
                        @click.native="toggleMobileMenu(false)"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>
                <div class="mt-10 space-y-3 text-sm text-white/60">
                    <RouterLink
                        v-for="item in utilityNavigation"
                        :key="item.to"
                        :to="item.to"
                        class="block transition hover:text-white"
                        @click.native="toggleMobileMenu(false)"
                    >
                        {{ item.label }}
                    </RouterLink>
                </div>
            </div>
        </transition>
    </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import MegaMenu from '@/components/navigation/MegaMenu.vue';
import { primaryNavigation, utilityNavigation } from '@/data/navigation';
import { useUiStore } from '@/stores/ui';

const ui = useUiStore();
const route = useRoute();

const handleMegaEnter = (item) => {
    if (!item.columns) {
        ui.closeMegaMenu();
        return;
    }
    ui.setActiveMegaMenu(item);
};

const handleMegaLeave = () => {
    ui.closeMegaMenu();
};

const closeMegaMenu = () => ui.closeMegaMenu();
const toggleMobileMenu = (force) => ui.toggleMobileMenu(force);
const toggleCartDrawer = () => ui.toggleCartDrawer();

const activeMegaSection = computed(() => ui.activeMegaMenu);

watch(
    () => route.fullPath,
    () => {
        ui.closeMegaMenu();
        ui.toggleMobileMenu(false);
    }
);

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        ui.closeMegaMenu();
        ui.toggleMobileMenu(false);
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscape);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

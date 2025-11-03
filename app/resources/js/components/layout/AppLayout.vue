<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="pointer-events-none fixed inset-x-0 top-0 z-[-1] h-80 bg-gradient-to-b from-emerald-700/10 via-slate-900/40 to-transparent blur-3xl"></div>

        <AppHeader />

        <main class="mx-auto w-full max-w-6xl px-6 py-12 lg:py-16">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue';
import AppFooter from './AppFooter.vue';
</script>

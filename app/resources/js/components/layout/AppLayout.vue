<template>
    <div class="min-h-screen bg-slate-100 text-slate-800">
        <div class="pointer-events-none fixed inset-x-0 top-0 z-[-1] h-80 bg-gradient-to-b from-sky-100 via-white to-transparent blur-3xl"></div>

        <AppHeader />

        <main class="mx-auto w-full max-w-6xl px-6 py-12 lg:py-16">
            <slot />
        </main>

        <AppFooter />

        <CartDrawer />
    </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue';
import AppFooter from './AppFooter.vue';
import CartDrawer from '@/components/cart/CartDrawer.vue';
</script>

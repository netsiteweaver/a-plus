<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-white to-slate-100 p-6">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/90 shadow-xl backdrop-blur">
            <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                    <span class="text-lg font-semibold">A+</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Admin Access</span>
                    <span class="text-lg font-semibold text-slate-900">A+ Commerce Backoffice</span>
                </div>
            </div>
            <div class="px-6 py-8">
                <slot />
            </div>
        </div>
        <p class="mt-6 text-xs text-slate-400">&copy; {{ year }} A+ Commerce. All rights reserved.</p>
    </div>
</template>

<script setup>
const year = new Date().getFullYear();
</script>

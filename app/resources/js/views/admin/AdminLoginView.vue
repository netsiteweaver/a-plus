<template>
    <form class="space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-600">Email</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200"
                placeholder="admin@example.com"
            />
        </div>

        <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-600">Password</label>
            <input
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200"
                placeholder="••••••••"
            />
        </div>

        <div class="flex items-center justify-between text-sm text-slate-500">
            <label class="inline-flex items-center gap-2">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                <span>Remember me</span>
            </label>
            <span class="cursor-not-allowed font-medium text-slate-300">Forgot password?</span>
        </div>

        <div v-if="auth.error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ auth.error }}
        </div>

        <button
            type="submit"
            :disabled="auth.status === 'loading'"
            class="flex w-full items-center justify-center rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:bg-sky-300"
        >
            <svg
                v-if="auth.status === 'loading'"
                class="mr-2 h-4 w-4 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5L12 3m0 0L7.5 7.5M12 3v18" />
            </svg>
            Sign in to dashboard
        </button>

        <p class="text-center text-xs text-slate-400">
            Need help? Contact your platform administrator to manage roles and access.
        </p>
    </form>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const form = reactive({
    email: 'admin@example.com',
    password: '',
    remember: true,
});

async function handleSubmit() {
    try {
        await auth.login({
            email: form.email,
            password: form.password,
            remember: form.remember,
        });

        const redirectTo = route.query.redirect ?? { name: 'admin.dashboard' };
        router.replace(redirectTo);
    } catch (error) {
        console.error('Failed to authenticate', error);
    }
}
</script>

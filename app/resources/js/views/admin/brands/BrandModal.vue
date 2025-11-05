<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ brand ? 'Edit brand' : 'Create brand' }}</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <form class="grid gap-4 px-6 py-6" @submit.prevent="submit">
                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Name
                    <input
                        v-model="form.name"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Slug
                    <input
                        v-model="form.slug"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Website URL
                    <input
                        v-model="form.website_url"
                        type="url"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="https://"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Logo URL
                    <input
                        v-model="form.logo_url"
                        type="url"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Description
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    ></textarea>
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Active brand
                </label>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="$emit('close')">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-sky-500 disabled:cursor-not-allowed disabled:bg-sky-300"
                    >
                        <svg
                            v-if="saving"
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5L12 3m0 0L7.5 7.5M12 3v18" />
                        </svg>
                        Save brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    brand: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.brand));

watch(
    () => props.brand,
    (next) => {
        Object.assign(form, createInitialState(next));
    },
    { immediate: true }
);

function createInitialState(brand) {
    return {
        name: brand?.name ?? '',
        slug: brand?.slug ?? '',
        website_url: brand?.website_url ?? '',
        logo_url: brand?.logo_url ?? '',
        description: brand?.description ?? '',
        is_active: brand?.is_active ?? true,
    };
}

function submit() {
    emit('save', {
        name: form.name,
        slug: form.slug || null,
        website_url: form.website_url || null,
        logo_url: form.logo_url || null,
        description: form.description || null,
        is_active: form.is_active,
    });
}
</script>

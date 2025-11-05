<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ option ? 'Edit option' : 'Create option' }}</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <form class="grid gap-4 px-6 py-6" @submit.prevent="submit">
                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Display name
                    <input
                        v-model="form.name"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Code
                    <input
                        v-model="form.code"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="color"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Input type
                    <select
                        v-model="form.input_type"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="select">Select list</option>
                        <option value="swatch">Color swatch</option>
                        <option value="radio">Radio buttons</option>
                        <option value="checkbox">Checkboxes</option>
                    </select>
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_required"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Required for add to cart
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Position
                    <input
                        v-model.number="form.position"
                        type="number"
                        min="0"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
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
                        Save option
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    option: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.option));

watch(
    () => props.option,
    (next) => {
        Object.assign(form, createInitialState(next));
    }
);

function createInitialState(option) {
    return {
        name: option?.name ?? '',
        code: option?.code ?? '',
        input_type: option?.input_type ?? 'select',
        is_required: option?.is_required ?? true,
        position: option?.position ?? 0,
    };
}

function submit() {
    emit('save', { ...form });
}
</script>

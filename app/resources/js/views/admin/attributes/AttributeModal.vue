<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ attribute ? 'Edit attribute' : 'Create attribute' }}</h3>
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
                    Code
                    <input
                        v-model="form.code"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="attribute_code"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Type
                    <select
                        v-model="form.type"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="text">Text</option>
                        <option value="textarea">Text area</option>
                        <option value="number">Number</option>
                        <option value="boolean">Boolean</option>
                        <option value="select">Select</option>
                        <option value="json">JSON</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Unit (optional)
                    <input
                        v-model="form.unit"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="GHz, lbs, etc."
                    />
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_filterable"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Filterable
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_required"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Required field
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
                        Save attribute
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    attribute: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.attribute));

watch(
    () => props.attribute,
    (next) => {
        Object.assign(form, createInitialState(next));
    }
);

function createInitialState(attribute) {
    return {
        name: attribute?.name ?? '',
        code: attribute?.code ?? '',
        type: attribute?.type ?? 'text',
        unit: attribute?.unit ?? '',
        is_filterable: attribute?.is_filterable ?? true,
        is_required: attribute?.is_required ?? false,
    };
}

function submit() {
    emit('save', {
        name: form.name,
        code: form.code,
        type: form.type,
        unit: form.unit || null,
        is_filterable: form.is_filterable,
        is_required: form.is_required,
    });
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ value ? 'Edit attribute value' : 'Add attribute' }}</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <form class="grid gap-4 px-6 py-6" @submit.prevent="submit">
                <label v-if="!value" class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Attribute
                    <select
                        v-model="form.attribute_id"
                        required
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="">Select attribute</option>
                        <option v-for="attribute in attributes" :key="attribute.id" :value="attribute.id">{{ attribute.name }}</option>
                    </select>
                </label>

                <template v-if="selectedAttribute?.values?.length">
                    <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Preset value
                        <select
                            v-model="form.attribute_value_id"
                            class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="">Custom entry</option>
                            <option v-for="preset in selectedAttribute.values" :key="preset.id" :value="preset.id">{{ preset.display_value ?? preset.value }}</option>
                        </select>
                    </label>
                </template>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Freeform text value
                    <input
                        v-model="form.value_text"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Numeric value
                    <input
                        v-model.number="form.value_number"
                        type="number"
                        step="0.01"
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
                        Save attribute
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, watch } from 'vue';

const props = defineProps({
    attributes: {
        type: Array,
        default: () => [],
    },
    value: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.value));

watch(
    () => props.value,
    (next) => {
        Object.assign(form, createInitialState(next));
    }
);

watch(
    () => form.attribute_value_id,
    (next) => {
        if (next) {
            form.value_text = '';
        }
    }
);

const selectedAttribute = computed(() => {
    const id = form.attribute_id ?? props.value?.attribute_id;
    return props.attributes.find((attribute) => attribute.id === id);
});

function createInitialState(value) {
    return {
        attribute_id: value?.attribute_id ?? '',
        attribute_value_id: value?.attribute_value_id ?? '',
        value_text: value?.value_text ?? '',
        value_number: value?.value_number ?? null,
    };
}

function submit() {
    emit('save', {
        attribute_id: form.attribute_id || props.value?.attribute_id,
        attribute_value_id: form.attribute_value_id || null,
        value_text: form.value_text || null,
        value_number: form.value_number ?? null,
    });
}
</script>

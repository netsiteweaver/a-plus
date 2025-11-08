<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
                    <p class="text-sm text-slate-500">Set variant pricing, inventory posture, and option mapping.</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <form class="grid gap-4 px-6 py-6 lg:grid-cols-2" @submit.prevent="submit">
                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    SKU
                    <input
                        v-model="form.sku"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Status
                    <select
                        v-model="form.status"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Price (USD)
                    <input
                        v-model.number="form.price"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Compare at price
                    <input
                        v-model.number="form.compare_at_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Inventory quantity
                    <input
                        v-model.number="form.inventory_quantity"
                        type="number"
                        min="0"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Inventory SKU
                    <input
                        v-model="form.inventory_sku"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <template v-for="option in options" :key="option.id">
                    <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ option.name }}
                        <select
                            v-model="form.option_value_ids[option.id]"
                            class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="">Unassigned</option>
                            <option v-for="value in option.values" :key="value.id" :value="value.id">{{ value.display_value ?? value.value }}</option>
                        </select>
                    </label>
                </template>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_default"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Set as default variant
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.requires_shipping"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Requires shipping
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.track_inventory"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Track inventory
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.requires_serial"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Requires serial
                </label>

                <div class="lg:col-span-2 flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="$emit('close')">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 hover:bg-sky-500 disabled:cursor-not-allowed disabled:bg-sky-300"
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
                        Save variant
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, watchEffect } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    variant: {
        type: Object,
        default: null,
    },
    options: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.variant, props.options));

watchEffect(() => {
    Object.assign(form, createInitialState(props.variant, props.options));
});

watchEffect(() => {
    const optionIds = Object.keys(form.option_value_ids);
    props.options.forEach((option) => {
        if (! optionIds.includes(String(option.id))) {
            form.option_value_ids[option.id] = '';
        }
    });
});

function createInitialState(variant, options) {
    const optionValues = {};
    options.forEach((option) => {
        optionValues[option.id] = variant?.option_values?.find((value) => value.product_option_id === option.id)?.id ?? '';
    });

    return {
        sku: variant?.sku ?? '',
        status: variant?.status ?? 'draft',
        price: variant?.price ?? 0,
        compare_at_price: variant?.compare_at_price ?? null,
        inventory_quantity: variant?.inventory_quantity ?? 0,
        inventory_sku: variant?.inventory_sku ?? '',
        is_default: variant?.is_default ?? false,
        requires_shipping: variant?.requires_shipping ?? true,
        requires_serial: variant?.requires_serial ?? false,
        track_inventory: variant?.track_inventory ?? true,
        option_value_ids: optionValues,
    };
}

function submit() {
    emit('save', {
        sku: form.sku,
        status: form.status,
        price: form.price,
        compare_at_price: form.compare_at_price || null,
        inventory_quantity: form.inventory_quantity,
        inventory_sku: form.inventory_sku || null,
        is_default: form.is_default,
        requires_shipping: form.requires_shipping,
        requires_serial: form.requires_serial,
        track_inventory: form.track_inventory,
        option_value_ids: Object.values(form.option_value_ids).filter(Boolean),
    });
}
</script>

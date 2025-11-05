<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Variants</h2>
                <p class="text-sm text-slate-500">Manage purchasable SKUs, pricing, and inventory posture.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                @click="openCreate"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add variant
            </button>
        </header>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Options</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Inventory</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!variants.length">
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No variants defined. Create at least one to publish this product.</td>
                    </tr>
                    <tr v-for="variant in variants" :key="variant.id" class="hover:bg-slate-50/75">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">{{ variant.sku }}</p>
                            <p class="text-xs text-slate-500">{{ variant.id }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ formatOptions(variant.option_values ?? []) }}</td>
                        <td class="px-4 py-4 text-slate-600">
                            ${{ Number(variant.price).toFixed(2) }}
                            <span v-if="variant.compare_at_price" class="ml-2 text-xs text-slate-400 line-through">${{ Number(variant.compare_at_price).toFixed(2) }}</span>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ variant.inventory_quantity }} in stock</td>
                        <td class="px-4 py-4">
                            <span :class="statusBadge(variant)">{{ variant.status }}{{ variant.is_default ? ' · default' : '' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                                    @click="openEdit(variant)"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="!variant.is_default"
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-50"
                                    @click="setDefault(variant)"
                                >
                                    Set default
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                                    @click="confirmDelete(variant)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <teleport to="body">
            <VariantModal
                v-if="variantModal.visible"
                :title="variantModal.mode === 'create' ? 'Create variant' : 'Edit variant'"
                :variant="variantModal.data"
                :options="options"
                :saving="variantModal.saving"
                @save="persistVariant"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete variant</h3>
                    <p class="mt-2 text-sm text-slate-600">Are you sure you want to delete variant <span class="font-semibold text-slate-900">{{ pendingDelete.sku }}</span>? This action cannot be undone.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyVariant">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { catalogApi } from '@/services/admin/catalog';
import VariantModal from './VariantModal.vue';

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },
    variants: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const variantModal = reactive({
    visible: false,
    mode: 'create',
    data: null,
    saving: false,
});

const pendingDelete = ref(null);

function openCreate() {
    variantModal.visible = true;
    variantModal.mode = 'create';
    variantModal.data = null;
}

function openEdit(variant) {
    variantModal.visible = true;
    variantModal.mode = 'edit';
    variantModal.data = variant;
}

function closeModal() {
    variantModal.visible = false;
    variantModal.data = null;
}

async function persistVariant(payload) {
    variantModal.saving = true;
    try {
        if (variantModal.mode === 'create') {
            await catalogApi.createVariant(props.productId, payload);
        } else {
            await catalogApi.updateVariant(props.productId, variantModal.data.id, payload);
        }
        closeModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save variant');
    } finally {
        variantModal.saving = false;
    }
}

function confirmDelete(variant) {
    pendingDelete.value = variant;
}

async function destroyVariant() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteVariant(props.productId, pendingDelete.value.id);
        pendingDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete variant');
    }
}

async function setDefault(variant) {
    try {
        await catalogApi.updateVariant(props.productId, variant.id, { is_default: true });
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Unable to set default variant');
    }
}

function formatOptions(optionValues) {
    if (!optionValues.length) return '—';
    return optionValues.map((value) => value.display_value ?? value.value).join(', ');
}

function statusBadge(variant) {
    const base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold capitalize ';
    if (variant.status === 'published') return base + 'bg-green-100 text-green-600';
    if (variant.status === 'archived') return base + 'bg-slate-100 text-slate-500';
    return base + 'bg-amber-100 text-amber-600';
}
</script>

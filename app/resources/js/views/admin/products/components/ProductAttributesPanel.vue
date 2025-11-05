<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Attributes</h2>
                <p class="text-sm text-slate-500">Structured specifications surfaced on the PDP and support filtering.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add attribute
            </button>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="item in attributeValues"
                :key="item.id"
                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ item.attribute?.name }}</p>
                        <p class="text-xs text-slate-500">Code {{ item.attribute?.code }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500 hover:border-sky-200 hover:text-sky-600"
                        @click="openModal(item)"
                    >
                        Edit
                    </button>
                </div>

                <p class="mt-2 text-sm text-slate-700">{{ displayValue(item) }}</p>

                <button
                    type="button"
                    class="mt-3 rounded-lg border border-red-200 px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50"
                    @click="confirmDelete(item)"
                >
                    Remove attribute
                </button>
            </article>

            <div v-if="!attributeValues.length" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                No technical specs captured yet. Map attributes to highlight differentiators and fuel filtering.
            </div>
        </div>

        <teleport to="body">
            <AttributeValueModal
                v-if="modal.visible"
                :attributes="attributes"
                :value="modal.value"
                :saving="modal.saving"
                @save="persistAttribute"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Remove attribute</h3>
                    <p class="mt-2 text-sm text-slate-600">Remove <span class="font-semibold text-slate-900">{{ pendingDelete.attribute?.name }}</span> from this product?</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyAttribute">
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
import AttributeValueModal from './AttributeValueModal.vue';

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },
    attributeValues: {
        type: Array,
        default: () => [],
    },
    attributes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const modal = reactive({ visible: false, value: null, saving: false });
const pendingDelete = ref(null);

function openModal(value = null) {
    modal.visible = true;
    modal.value = value;
}

function closeModal() {
    modal.visible = false;
    modal.value = null;
}

async function persistAttribute(payload) {
    modal.saving = true;
    try {
        if (modal.value) {
            await catalogApi.updateProductAttribute(props.productId, modal.value.id, payload);
        } else {
            await catalogApi.upsertProductAttribute(props.productId, payload);
        }
        closeModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save attribute');
    } finally {
        modal.saving = false;
    }
}

function confirmDelete(value) {
    pendingDelete.value = value;
}

async function destroyAttribute() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteProductAttribute(props.productId, pendingDelete.value.id);
        pendingDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to remove attribute');
    }
}

function displayValue(item) {
    if (item.attribute_value) {
        return item.attribute_value.display_value ?? item.attribute_value.value;
    }

    if (item.value_text) return item.value_text;
    if (item.value_number !== null && item.value_number !== undefined) return item.value_number;
    if (item.value_json) return JSON.stringify(item.value_json);
    return '—';
}
</script>

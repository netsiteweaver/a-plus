<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Related products</h2>
                <p class="text-sm text-slate-500">Curate upsells, cross-sells, and accessory pairings.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add relation
            </button>
        </header>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-left">Relation</th>
                        <th class="px-4 py-3 text-left">Position</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!related.length">
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No related products configured.</td>
                    </tr>
                    <tr v-for="item in related" :key="item.id" class="hover:bg-slate-50/75">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">{{ item.related_product?.name ?? 'Unknown' }}</p>
                            <p class="text-xs text-slate-500">ID {{ item.related_product_id }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ formatRelation(item.relation_type) }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ item.position }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                                    @click="openModal(item)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                                    @click="confirmDelete(item)"
                                >
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <teleport to="body">
            <RelatedModal
                v-if="modal.visible"
                :products="products"
                :relation="modal.data"
                :saving="modal.saving"
                @save="persistRelation"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Remove related product</h3>
                    <p class="mt-2 text-sm text-slate-600">Remove {{ pendingDelete.related_product?.name ?? 'this product' }} from the related list?</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyRelation">
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
import RelatedModal from './RelatedModal.vue';

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },
    related: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const modal = reactive({ visible: false, data: null, saving: false });
const pendingDelete = ref(null);

function openModal(relation = null) {
    modal.visible = true;
    modal.data = relation;
}

function closeModal() {
    modal.visible = false;
    modal.data = null;
}

async function persistRelation(payload) {
    modal.saving = true;
    try {
        if (modal.data) {
            await catalogApi.updateRelatedProduct(props.productId, modal.data.id, payload);
        } else {
            await catalogApi.createRelatedProduct(props.productId, payload);
        }
        closeModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save related product');
    } finally {
        modal.saving = false;
    }
}

function confirmDelete(relation) {
    pendingDelete.value = relation;
}

async function destroyRelation() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteRelatedProduct(props.productId, pendingDelete.value.id);
        pendingDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to remove related product');
    }
}

function formatRelation(type) {
    switch (type) {
        case 'upsell':
            return 'Upsell';
        case 'accessory':
            return 'Accessory';
        case 'replacement':
            return 'Replacement';
        case 'bundle':
            return 'Bundle';
        default:
            return 'Related';
    }
}
</script>

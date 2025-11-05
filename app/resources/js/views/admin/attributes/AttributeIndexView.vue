<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Attributes</h1>
                <p class="text-sm text-slate-500">Define structured specifications available during product enrichment.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-500"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                New attribute
            </button>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Attribute</th>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Values</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading attributes…</td>
                    </tr>
                    <tr v-for="attribute in attributes" :key="attribute.id" class="hover:bg-slate-50/75">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">{{ attribute.name }}</p>
                            <p class="text-xs text-slate-500">{{ attribute.is_filterable ? 'Filterable' : 'Not filterable' }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ attribute.code }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ attribute.type }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ attribute.values?.length ?? 0 }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                                    @click="openModal(attribute)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                                    @click="confirmDelete(attribute)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <teleport to="body">
            <AttributeModal
                v-if="modal.visible"
                :attribute="modal.data"
                :saving="modal.saving"
                @save="persistAttribute"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete attribute</h3>
                    <p class="mt-2 text-sm text-slate-600">Deleting <span class="font-semibold text-slate-900">{{ pendingDelete.name }}</span> will remove associated product specs.</p>
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
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { catalogApi } from '@/services/admin/catalog';
import AttributeModal from './AttributeModal.vue';

const attributes = ref([]);
const loading = ref(true);
const modal = reactive({ visible: false, data: null, saving: false });
const pendingDelete = ref(null);

onMounted(async () => {
    await loadAttributes();
});

async function loadAttributes() {
    loading.value = true;
    try {
        const response = await catalogApi.listAttributes({ per_page: 200, include_values: true });
        attributes.value = response.data?.data ?? [];
    } finally {
        loading.value = false;
    }
}

function openModal(attribute = null) {
    modal.visible = true;
    modal.data = attribute;
}

function closeModal() {
    modal.visible = false;
    modal.data = null;
}

async function persistAttribute(payload) {
    modal.saving = true;
    try {
        if (modal.data) {
            await catalogApi.updateAttribute(modal.data.id, payload);
        } else {
            await catalogApi.createAttribute(payload);
        }
        closeModal();
        await loadAttributes();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save attribute');
    } finally {
        modal.saving = false;
    }
}

function confirmDelete(attribute) {
    pendingDelete.value = attribute;
}

async function destroyAttribute() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteAttribute(pendingDelete.value.id);
        pendingDelete.value = null;
        await loadAttributes();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete attribute');
    }
}
</script>

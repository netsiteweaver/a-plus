<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Brands</h1>
                <p class="text-sm text-slate-500">Manage manufacturer metadata, logos, and merchandising visibility.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-500"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                New brand
            </button>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-left">Slug</th>
                        <th class="px-4 py-3 text-left">Website</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading brands…</td>
                    </tr>
                    <tr v-for="brand in brands" :key="brand.id" class="hover:bg-slate-50/75">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">{{ brand.name }}</p>
                            <p class="text-xs text-slate-500">{{ brand.meta_title ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ brand.slug }}</td>
                        <td class="px-4 py-4 text-slate-600">
                            <a v-if="brand.website_url" :href="brand.website_url" target="_blank" class="text-sky-600 hover:text-sky-500">{{ brand.website_url }}</a>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-4">
                            <span :class="brand.is_active ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-600' : 'rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500'">
                                {{ brand.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                                    @click="openModal(brand)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                                    @click="confirmDelete(brand)"
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
            <BrandModal
                v-if="modal.visible"
                :brand="modal.data"
                :saving="modal.saving"
                @save="persistBrand"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete brand</h3>
                    <p class="mt-2 text-sm text-slate-600">Delete <span class="font-semibold text-slate-900">{{ pendingDelete.name }}</span>? Products remain but lose brand association.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyBrand">
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
import BrandModal from './BrandModal.vue';

const brands = ref([]);
const loading = ref(true);
const modal = reactive({ visible: false, data: null, saving: false });
const pendingDelete = ref(null);

onMounted(async () => {
    await loadBrands();
});

async function loadBrands() {
    loading.value = true;
    try {
        const response = await catalogApi.listBrands({ per_page: 100 });
        brands.value = response.data?.data ?? [];
    } finally {
        loading.value = false;
    }
}

function openModal(brand = null) {
    modal.visible = true;
    modal.data = brand;
}

function closeModal() {
    modal.visible = false;
    modal.data = null;
}

async function persistBrand(payload) {
    modal.saving = true;
    try {
        if (modal.data) {
            await catalogApi.updateBrand(modal.data.id, payload);
        } else {
            await catalogApi.createBrand(payload);
        }
        closeModal();
        await loadBrands();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save brand');
    } finally {
        modal.saving = false;
    }
}

function confirmDelete(brand) {
    pendingDelete.value = brand;
}

async function destroyBrand() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteBrand(pendingDelete.value.id);
        pendingDelete.value = null;
        await loadBrands();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete brand');
    }
}
</script>

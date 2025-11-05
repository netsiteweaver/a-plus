<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Media library</h2>
                <p class="text-sm text-slate-500">Manage hero imagery, galleries, and variant-specific assets.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add media
            </button>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="item in media"
                :key="item.id"
                class="rounded-xl border border-slate-200 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ item.type }}</p>
                        <p class="text-xs text-slate-500">{{ item.url ?? item.path }}</p>
                    </div>
                    <span v-if="item.is_primary" class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-600">Primary</span>
                </div>

                <p v-if="item.product_variant_id" class="mt-2 text-xs text-slate-500">Variant ID {{ item.product_variant_id }}</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500 hover:border-sky-200 hover:text-sky-600"
                        @click="openModal(item)"
                    >
                        Edit
                    </button>
                    <button
                        v-if="!item.is_primary"
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500 hover:bg-slate-50"
                        @click="markPrimary(item)"
                    >
                        Set primary
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-red-200 px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50"
                        @click="confirmDelete(item)"
                    >
                        Delete
                    </button>
                </div>
            </article>

            <div v-if="!media.length" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                No media assets yet. Add hosted URLs or upload references for PDP galleries.
            </div>
        </div>

        <teleport to="body">
            <MediaModal
                v-if="modal.visible"
                :media="modal.data"
                :variants="variants"
                :saving="modal.saving"
                @save="persistMedia"
                @close="closeModal"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Remove media</h3>
                    <p class="mt-2 text-sm text-slate-600">Delete selected media asset? This cannot be undone.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyMedia">
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
import MediaModal from './MediaModal.vue';

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },
    media: {
        type: Array,
        default: () => [],
    },
    variants: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const modal = reactive({ visible: false, data: null, saving: false });
const pendingDelete = ref(null);

function openModal(item = null) {
    modal.visible = true;
    modal.data = item;
}

function closeModal() {
    modal.visible = false;
    modal.data = null;
}

async function persistMedia(payload) {
    modal.saving = true;
    try {
        if (modal.data) {
            await catalogApi.updateMedia(props.productId, modal.data.id, payload);
        } else {
            await catalogApi.createMedia(props.productId, payload);
        }
        closeModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save media');
    } finally {
        modal.saving = false;
    }
}

async function markPrimary(item) {
    try {
        await catalogApi.updateMedia(props.productId, item.id, { is_primary: true });
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Unable to update media');
    }
}

function confirmDelete(item) {
    pendingDelete.value = item;
}

async function destroyMedia() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteMedia(props.productId, pendingDelete.value.id);
        pendingDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete media');
    }
}
</script>

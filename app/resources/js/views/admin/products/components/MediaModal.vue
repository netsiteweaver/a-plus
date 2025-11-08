<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ media ? 'Edit media' : 'Upload media' }}</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <form class="grid gap-4 px-6 py-6" @submit.prevent="submit">
                <!-- File Upload (only for new media) -->
                <div v-if="!media" class="space-y-2">
                    <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Image File
                    </label>
                    <div
                        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 transition-colors hover:border-sky-400 hover:bg-sky-50"
                        :class="{ 'border-sky-400 bg-sky-50': isDragging }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleFileDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            @change="handleFileSelect"
                        />
                        
                        <div v-if="!selectedFile" class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-slate-700">Drop image here or click to select</p>
                            <p class="mt-1 text-xs text-slate-500">PNG, JPG, GIF up to 10MB</p>
                        </div>

                        <div v-else class="text-center">
                            <img v-if="previewUrl" :src="previewUrl" alt="Preview" class="mx-auto mb-3 h-32 w-32 rounded-lg object-cover" />
                            <p class="text-sm font-semibold text-slate-700">{{ selectedFile.name }}</p>
                            <p class="text-xs text-slate-500">{{ formatFileSize(selectedFile.size) }}</p>
                            <button
                                type="button"
                                class="mt-2 text-xs font-medium text-red-500 hover:text-red-600"
                                @click.stop="clearFile"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Existing media info (for edit mode) -->
                <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <img v-if="media.url" :src="media.url" alt="Current image" class="mb-2 h-32 w-full rounded-lg object-cover" />
                    <p class="text-xs text-slate-500">{{ media.path }}</p>
                </div>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Type
                    <select
                        v-model="form.type"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        :disabled="!!media"
                    >
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="document">Document</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Alt text
                    <input
                        v-model="form.alt_text"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="Descriptive text for accessibility"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Caption
                    <textarea
                        v-model="form.caption"
                        rows="2"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="Optional caption"
                    ></textarea>
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

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Variant binding
                    <select
                        v-model="form.product_variant_id"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="">All variants</option>
                        <option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.sku }}</option>
                    </select>
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <input
                        v-model="form.is_primary"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    />
                    Mark as featured image
                </label>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="$emit('close')">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving || (!media && !selectedFile)"
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
                        {{ media ? 'Save changes' : 'Upload' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';

const props = defineProps({
    media: {
        type: Object,
        default: null,
    },
    variants: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close']);

const form = reactive(createInitialState(props.media));
const selectedFile = ref(null);
const previewUrl = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);

watch(
    () => props.media,
    (next) => {
        Object.assign(form, createInitialState(next));
        selectedFile.value = null;
        previewUrl.value = null;
    },
    { immediate: true }
);

function createInitialState(media) {
    return {
        type: media?.type ?? 'image',
        alt_text: media?.alt_text ?? '',
        caption: media?.caption ?? '',
        position: media?.position ?? 0,
        product_variant_id: media?.product_variant_id ?? '',
        is_primary: media?.is_primary ?? false,
    };
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        setSelectedFile(file);
    }
}

function handleFileDrop(event) {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        setSelectedFile(file);
    }
}

function setSelectedFile(file) {
    selectedFile.value = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function clearFile() {
    selectedFile.value = null;
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function submit() {
    emit('save', {
        file: selectedFile.value,
        type: form.type,
        alt_text: form.alt_text || null,
        caption: form.caption || null,
        position: form.position,
        product_variant_id: form.product_variant_id || null,
        is_primary: form.is_primary,
    });
}
</script>

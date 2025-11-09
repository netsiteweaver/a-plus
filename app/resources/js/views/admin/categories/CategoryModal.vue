<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ category ? 'Edit category' : 'Create category' }}</h3>
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
                        ref="nameInput"
                        v-model="form.name"
                        required
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Slug
                    <div class="flex gap-2">
                        <input
                            v-model="form.slug"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            placeholder="Auto-generated from name"
                            @input="handleSlugInput"
                        />
                        <button
                            v-if="category && form.slug"
                            type="button"
                            class="rounded-xl border border-purple-200 bg-purple-50 px-3 py-2 text-xs font-medium text-purple-600 transition hover:bg-purple-100"
                            @click="confirmRegenerateSlug"
                            title="Regenerate slug from name"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                    <span class="text-xs normal-case text-slate-500">
                        {{ category ? 'Click the refresh button to regenerate from name' : 'Leave blank to auto-generate from name' }}
                    </span>
                </label>

                <ImageUpload
                    ref="imageUpload"
                    v-model="form.image_url"
                    label="Category Image"
                    help-text="Upload an image for this category (JPG, PNG, GIF, SVG, or WEBP, max 5MB)"
                    :max-size="5120"
                    accept-text="PNG, JPG, GIF, SVG, WEBP up to 5MB"
                    @upload="handleImageUpload"
                    @delete="handleImageDelete"
                />

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Parent
                    <select
                        v-model="form.parent_id"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="">Top level</option>
                        <option
                            v-for="node in parentCandidates"
                            :key="node.id"
                            :value="node.id"
                        >
                            {{ '—'.repeat(node.depth) }} {{ node.name }}
                        </option>
                    </select>
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
                    Position
                    <input
                        v-model.number="form.position"
                        type="number"
                        min="0"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Description
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    ></textarea>
                </label>

                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700 cursor-pointer">
                        <input
                            v-model="form.is_featured"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                        />
                        <div>
                            <span class="font-semibold">Featured Category</span>
                            <p class="text-xs text-slate-500">Display this category on the homepage</p>
                        </div>
                    </label>
                </div>

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
                        Save category
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, watch, ref, onMounted, nextTick } from 'vue';
import { api } from '@/services/http';
import ImageUpload from '@/components/admin/ImageUpload.vue';

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'close', 'refresh']);

const form = reactive(createInitialState(props.category));
const nameInput = ref(null);
const imageUpload = ref(null);
const pendingImageFile = ref(null);
const slugManuallyEdited = ref(false);

// Auto-generate slug from name
watch(
    () => form.name,
    (newName) => {
        // Auto-generate if slug hasn't been manually edited and it's a new category
        if (!slugManuallyEdited.value && !props.category) {
            form.slug = generateSlug(newName);
        }
    }
);

// Track manual slug edits
const handleSlugInput = () => {
    slugManuallyEdited.value = true;
};

// Generate slug from a given string
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-'); // Replace multiple hyphens with single hyphen
}

// Confirm and regenerate slug for existing category
function confirmRegenerateSlug() {
    if (confirm('Regenerate slug from category name? This will replace the current slug.')) {
        form.slug = generateSlug(form.name);
        slugManuallyEdited.value = false; // Reset so it can auto-update again
    }
}

watch(
    () => props.category,
    (next) => {
        Object.assign(form, createInitialState(next));
        pendingImageFile.value = null;
        slugManuallyEdited.value = false; // Reset when switching categories
        nextTick(() => {
            nameInput.value?.focus();
            imageUpload.value?.clearPreview();
        });
    },
    { immediate: true }
);

onMounted(() => {
    nextTick(() => {
        nameInput.value?.focus();
    });
});

const parentCandidates = computed(() => {
    return props.categories.filter((node) => node.id !== props.category?.id);
});

function createInitialState(category) {
    return {
        name: category?.name ?? '',
        slug: category?.slug ?? '',
        image_url: category?.image_url ?? '',
        parent_id: category?.parent_id ?? '',
        status: category?.status ?? 'draft',
        position: category?.position ?? 0,
        description: category?.description ?? '',
        is_featured: category?.is_featured ?? false,
    };
}

async function handleImageUpload(file) {
    // If editing existing category, upload immediately
    if (props.category?.id) {
        try {
            imageUpload.value?.setUploading(true);

            const formData = new FormData();
            formData.append('image', file);

            const response = await api.post(`/admin/categories/${props.category.id}/image`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            form.image_url = response.data.image_url;
            emit('refresh');
        } catch (error) {
            console.error('Failed to upload image:', error);
            imageUpload.value?.setError(error.response?.data?.message || 'Failed to upload image');
        } finally {
            imageUpload.value?.setUploading(false);
        }
    } else {
        // For new categories, store file to upload after category creation
        pendingImageFile.value = file;
    }
}

async function handleImageDelete() {
    if (props.category?.id) {
        try {
            await api.delete(`/admin/categories/${props.category.id}/image`);
            form.image_url = null;
            emit('refresh');
        } catch (error) {
            console.error('Failed to delete image:', error);
            imageUpload.value?.setError(error.response?.data?.message || 'Failed to delete image');
        }
    } else {
        pendingImageFile.value = null;
        form.image_url = null;
    }
}

function submit() {
    emit('save', {
        name: form.name,
        slug: form.slug || null,
        image_url: form.image_url || null,
        parent_id: form.parent_id || null,
        status: form.status,
        position: form.position,
        description: form.description || null,
        is_featured: form.is_featured,
    }, pendingImageFile.value);
}
</script>

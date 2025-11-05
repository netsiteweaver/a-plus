<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Product metadata</h2>
                <p class="text-sm text-slate-500">Core merchandising details and publishing controls.</p>
            </div>
            <span v-if="success" class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-600">Saved</span>
        </header>

        <form class="grid gap-4 lg:grid-cols-2" @submit.prevent="save">
            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400 lg:col-span-2">
                Name
                <input
                    v-model="form.name"
                    required
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Product name"
                />
            </label>

            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Slug
                <input
                    v-model="form.slug"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="product-slug"
                />
            </label>

            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                SKU
                <input
                    v-model="form.sku"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="SKU"
                />
            </label>

            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Brand
                <select
                    v-model="form.brand_id"
                    class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                >
                    <option value="">Unassigned</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
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
                Published at
                <input
                    v-model="form.published_at"
                    type="datetime-local"
                    class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                />
            </label>

            <label class="lg:col-span-2 flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Subtitle
                <input
                    v-model="form.subtitle"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Short subheading"
                />
            </label>

            <label class="lg:col-span-2 flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Excerpt
                <textarea
                    v-model="form.excerpt"
                    rows="3"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Brief blurb for listing cards"
                ></textarea>
            </label>

            <label class="lg:col-span-2 flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Description
                <textarea
                    v-model="form.description"
                    rows="6"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Long-form PDP copy"
                ></textarea>
            </label>

            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Meta title
                <input
                    v-model="form.meta_title"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                />
            </label>

            <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Meta description
                <textarea
                    v-model="form.meta_description"
                    rows="3"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                ></textarea>
            </label>

            <div class="lg:col-span-2 flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50"
                    @click="reset"
                >
                    Reset
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
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
                    Save changes
                </button>
            </div>
        </form>
    </section>
</template>

<script setup>
import { watchEffect, reactive, ref } from 'vue';
import { catalogApi } from '@/services/admin/catalog';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    brands: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const form = reactive(initState(props.product));
const saving = ref(false);
const success = ref(false);

watchEffect(() => {
    const next = props.product;
    if (! next) {
        return;
    }

    Object.assign(form, initState(next));
    success.value = false;
});

function initState(product) {
    return {
        name: product?.name ?? '',
        slug: product?.slug ?? '',
        sku: product?.sku ?? '',
        brand_id: product?.brand_id ?? '',
        status: product?.status ?? 'draft',
        subtitle: product?.subtitle ?? '',
        excerpt: product?.excerpt ?? '',
        description: product?.description ?? '',
        meta_title: product?.meta_title ?? '',
        meta_description: product?.meta_description ?? '',
        published_at: product?.published_at ? product.published_at.substring(0, 16) : '',
    };
}

function reset() {
    Object.assign(form, initState(props.product));
    success.value = false;
}

async function save() {
    saving.value = true;
    success.value = false;

    try {
        await catalogApi.updateProduct(props.product.id, {
            ...form,
            brand_id: form.brand_id || null,
            published_at: form.published_at ? new Date(form.published_at).toISOString() : null,
        });

        success.value = true;
        emit('updated');
    } finally {
        saving.value = false;
    }
}
</script>

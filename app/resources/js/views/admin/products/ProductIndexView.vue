<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Products</h1>
                <p class="text-sm text-slate-500">Curate the active catalog, manage merchandising metadata, and keep PDP content sharp.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-500"
                @click="openCreateModal"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                New product
            </button>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form class="grid gap-4 md:grid-cols-4" @submit.prevent="loadProducts(1)">
                <label class="col-span-2 flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Search
                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Name, SKU, slug"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-inner focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    />
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Status
                    <select
                        v-model="filters.status"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="">All statuses</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Brand
                    <select
                        v-model="filters.brand_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="">All brands</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                    </select>
                </label>

                <div class="md:col-span-4 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
                    >
                        Apply filters
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Updated</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading products…</td>
                        </tr>
                        <tr v-if="!loading && !products.length">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No products match the current filters.</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">{{ product.name }}</p>
                                    <p class="text-xs text-slate-500">SKU {{ product.sku ?? '—' }} · {{ product.slug }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-600">{{ product.brand?.name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-4">
                                <span :class="statusBadge(product.status)">{{ product.status }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-600">{{ formatTimestamp(product.updated_at) }}</td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <router-link
                                        :to="{ name: 'admin.products.show', params: { id: product.id } }"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                                    >
                                        Manage
                                    </router-link>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                                        @click="confirmDeletion(product)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer v-if="pagination.total > pagination.per_page" class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-xs text-slate-500">
                <p>
                    Showing
                    <span class="font-semibold text-slate-700">{{ pageStart }}</span>
                    &ndash;
                    <span class="font-semibold text-slate-700">{{ pageEnd }}</span>
                    of
                    <span class="font-semibold text-slate-700">{{ pagination.total }}</span>
                    records
                </p>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-50"
                        :disabled="pagination.current_page === 1"
                        @click="loadProducts(pagination.current_page - 1)"
                    >
                        Previous
                    </button>
                    <span class="font-semibold text-slate-700">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-50"
                        :disabled="pagination.current_page === pagination.last_page"
                        @click="loadProducts(pagination.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </footer>
        </section>

        <teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">Create product</h2>
                        <button type="button" class="text-slate-400 hover:text-slate-600" @click="closeCreateModal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form class="grid gap-4 px-6 py-6 lg:grid-cols-2" @submit.prevent="createProduct">
                        <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400 lg:col-span-2">
                            Name
                            <input
                                ref="nameInput"
                                v-model="createForm.name"
                                required
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                placeholder="Hyperbook Pro 15"
                            />
                        </label>

                        <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Slug
                            <input
                                v-model="createForm.slug"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                placeholder="hyperbook-pro-15"
                            />
                        </label>

                        <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            SKU
                            <input
                                v-model="createForm.sku"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                placeholder="SKU-001"
                            />
                        </label>

                        <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Brand
                            <select
                                v-model="createForm.brand_id"
                                class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            >
                                <option value="">Select brand</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Status
                            <select
                                v-model="createForm.status"
                                class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </label>

                        <label class="lg:col-span-2 flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Excerpt
                            <textarea
                                v-model="createForm.excerpt"
                                rows="3"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                placeholder="Short description for list views"
                            ></textarea>
                        </label>

                        <div v-if="createError" class="lg:col-span-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                            {{ createError }}
                        </div>

                        <div class="lg:col-span-2 flex items-center justify-end gap-2 pt-2">
                            <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="closeCreateModal">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createLoading"
                                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 hover:bg-sky-500 disabled:cursor-not-allowed disabled:bg-sky-300"
                            >
                                <svg
                                    v-if="createLoading"
                                    class="h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5L12 3m0 0L7.5 7.5M12 3v18" />
                                </svg>
                                Create product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </teleport>

        <teleport to="body">
            <div v-if="pendingDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete product</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        This will soft-delete <span class="font-semibold text-slate-900">{{ pendingDeletion.name }}</span> and remove it from storefront listings.
                        Variants, media, and related records are retained for audit recovery.
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDeletion = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="deleteProduct">
                            Confirm delete
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { catalogApi } from '@/services/admin/catalog';

const router = useRouter();

const loading = ref(false);
const products = ref([]);
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
});

const filters = reactive({
    search: '',
    status: '',
    brand_id: '',
});

const brands = ref([]);

const showCreate = ref(false);
const createForm = reactive({
    name: '',
    slug: '',
    sku: '',
    brand_id: '',
    status: 'draft',
    excerpt: '',
});
const createLoading = ref(false);
const createError = ref('');
const nameInput = ref(null);

const pendingDeletion = ref(null);

const pageStart = computed(() => {
    return (pagination.current_page - 1) * pagination.per_page + (products.value.length ? 1 : 0);
});

const pageEnd = computed(() => {
    return pageStart.value + products.value.length - 1;
});

watch(showCreate, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            nameInput.value?.focus();
        });
    }
});

onMounted(async () => {
    await Promise.all([loadBrands(), loadProducts()]);
});

async function loadProducts(page = pagination.current_page) {
    loading.value = true;
    try {
        const response = await catalogApi.listProducts({
            page,
            per_page: pagination.per_page,
            search: filters.search || undefined,
            status: filters.status || undefined,
            brand_id: filters.brand_id || undefined,
            include: ['brand'],
        });

        products.value = response.data?.data ?? [];
        const meta = response.data?.meta ?? {};
        pagination.current_page = meta.current_page ?? page;
        pagination.last_page = meta.last_page ?? 1;
        pagination.per_page = meta.per_page ?? pagination.per_page;
        pagination.total = meta.total ?? products.value.length;
    } finally {
        loading.value = false;
    }
}

async function loadBrands() {
    const response = await catalogApi.listBrands({ per_page: 100 });
    brands.value = response.data?.data ?? [];
}

function resetFilters() {
    filters.search = '';
    filters.status = '';
    filters.brand_id = '';
    loadProducts(1);
}

function statusBadge(status) {
    const base = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ';
    switch (status) {
        case 'published':
            return base + 'bg-green-100 text-green-600';
        case 'archived':
            return base + 'bg-slate-100 text-slate-500';
        default:
            return base + 'bg-amber-100 text-amber-600';
    }
}

function formatTimestamp(timestamp) {
    if (!timestamp) {
        return '—';
    }

    return new Date(timestamp).toLocaleString();
}

function openCreateModal() {
    showCreate.value = true;
    createError.value = '';
}

function closeCreateModal() {
    showCreate.value = false;
    createError.value = '';
    Object.assign(createForm, {
        name: '',
        slug: '',
        sku: '',
        brand_id: '',
        status: 'draft',
        excerpt: '',
    });
}

async function createProduct() {
    createLoading.value = true;
    createError.value = '';
    try {
        const response = await catalogApi.createProduct({
            name: createForm.name,
            slug: createForm.slug,
            sku: createForm.sku,
            brand_id: createForm.brand_id || null,
            status: createForm.status,
            excerpt: createForm.excerpt,
        });

        closeCreateModal();
        await loadProducts(1);
        router.push({ name: 'admin.products.show', params: { id: response.data?.data?.id ?? response.data?.id } });
    } catch (error) {
        createError.value = error.response?.data?.message ?? 'Unable to create product.';
    } finally {
        createLoading.value = false;
    }
}

function confirmDeletion(product) {
    pendingDeletion.value = product;
}

async function deleteProduct() {
    if (!pendingDeletion.value) return;

    try {
        await catalogApi.deleteProduct(pendingDeletion.value.id);
        pendingDeletion.value = null;
        await loadProducts();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete product');
    }
}
</script>

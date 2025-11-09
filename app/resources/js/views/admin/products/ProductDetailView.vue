<template>
    <div v-if="loading" class="flex min-h-[50vh] items-center justify-center text-slate-500">Loading product…</div>

    <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-6 text-red-600">
        {{ error }}
    </div>

    <div v-else class="space-y-6">
        <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Product detail</p>
                    <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ product.name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">UUID {{ product.uuid }} · SKU {{ product.sku ?? '—' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span :class="statusBadge(product.status)">{{ product.status }}</span>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50"
                        @click="refreshProduct"
                    >
                        Refresh data
                    </button>
                </div>
            </div>
        </header>

        <ProductMetadataForm
            :product="product"
            :brands="brands"
            @updated="refreshProduct"
        />

        <ProductCategoryPanel
            :product="product"
            :categories="categoryTree"
            @updated="refreshProduct"
        />

        <ProductVariantsPanel
            :product-id="product.id"
            :variants="product.variants ?? []"
            :options="product.options ?? []"
            @updated="refreshProduct"
        />

        <ProductOptionsPanel
            :product-id="product.id"
            :options="product.options ?? []"
            @updated="refreshProduct"
        />

        <ProductMediaPanel
            :product-id="product.id"
            :media="product.media ?? []"
            :variants="product.variants ?? []"
            @updated="refreshProduct"
        />

        <ProductAttributesPanel
            :product-id="product.id"
            :attribute-values="product.attribute_values ?? []"
            :attributes="attributes"
            @updated="refreshProduct"
        />

        <ProductRelatedPanel
            :product-id="product.id"
            :related="product.related_products ?? []"
            :products="relatedPool"
            @updated="refreshProduct"
        />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { catalogApi } from '@/services/admin/catalog';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import ProductMetadataForm from './components/ProductMetadataForm.vue';
import ProductCategoryPanel from './components/ProductCategoryPanel.vue';
import ProductVariantsPanel from './components/ProductVariantsPanel.vue';
import ProductOptionsPanel from './components/ProductOptionsPanel.vue';
import ProductMediaPanel from './components/ProductMediaPanel.vue';
import ProductAttributesPanel from './components/ProductAttributesPanel.vue';
import ProductRelatedPanel from './components/ProductRelatedPanel.vue';

const route = useRoute();
const { setDynamicBreadcrumb } = useBreadcrumbs();

const loading = ref(true);
const product = ref(null);
const error = ref('');

const brands = ref([]);
const categoryTree = ref([]);
const attributes = ref([]);
const relatedPool = ref([]);

onMounted(async () => {
    try {
        await Promise.all([refreshProduct(), loadBrands(), loadCategories(), loadAttributes(), loadRelatedPool()]);
    } catch (err) {
        console.error('Error loading product detail page:', err);
    }
});

async function refreshProduct() {
    loading.value = true;
    error.value = '';
    try {
        const response = await catalogApi.getProduct(route.params.id, {
            include: ['brand', 'categories', 'variants.option_values', 'options.values', 'media', 'attributeValues.attribute', 'attributeValues.attributeValue', 'relatedProducts.related'],
        });
        product.value = normalizeProduct(response.data?.data ?? response.data);
        
        // Update breadcrumb with product name
        if (product.value?.name) {
            setDynamicBreadcrumb(product.value.name);
        }
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load product.';
    } finally {
        loading.value = false;
    }
}

async function loadBrands() {
    try {
        const response = await catalogApi.listBrands({ per_page: 100 });
        brands.value = response.data?.data ?? [];
    } catch (err) {
        console.error('Error loading brands:', err);
    }
}

async function loadCategories() {
    try {
        const response = await catalogApi.listCategories({ tree: true });
        categoryTree.value = response.data?.data ?? response.data ?? [];
    } catch (err) {
        console.error('Error loading categories:', err);
    }
}

async function loadAttributes() {
    try {
        const response = await catalogApi.listAttributes({ per_page: 100, include_values: 1 });
        attributes.value = response.data?.data ?? [];
    } catch (err) {
        console.error('Error loading attributes:', err);
        if (err.response?.status === 422) {
            console.error('Validation error details:', err.response?.data);
        }
    }
}

async function loadRelatedPool() {
    try {
        const response = await catalogApi.listProducts({ per_page: 100, include: ['brand'] });
        const items = response.data?.data ?? [];
        relatedPool.value = items.filter((item) => item.id !== product.value?.id);
    } catch (err) {
        console.error('Error loading related products pool:', err);
        if (err.response?.status === 422) {
            console.error('Validation error details:', err.response?.data);
        }
    }
}

function normalizeProduct(raw) {
    if (!raw) return null;

    return {
        ...raw,
        variants: raw.variants ?? [],
        options: raw.options ?? [],
        media: raw.media ?? [],
        attribute_values: raw.attribute_values ?? [],
        related_products: raw.related_products ?? [],
    };
}

function statusBadge(status) {
    const base = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide ';
    switch (status) {
        case 'published':
            return base + 'bg-green-100 text-green-600';
        case 'archived':
            return base + 'bg-slate-100 text-slate-500';
        default:
            return base + 'bg-amber-100 text-amber-600';
    }
}
</script>

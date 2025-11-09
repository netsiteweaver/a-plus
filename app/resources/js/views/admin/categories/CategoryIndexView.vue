<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-500">Structure the navigation tree and control merchandising visibility.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-500"
                @click="openModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                New category
            </button>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Image</th>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Featured</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <CategoryRow
                            v-for="node in categories"
                            :key="`${node.id}-${node.updated_at}`"
                            :node="node"
                            :depth="0"
                            @edit="openModal"
                            @delete="confirmDelete"
                        />
                    </tbody>
                </table>
            </div>
        </section>

        <teleport to="body">
            <CategoryModal
                v-if="modal.visible"
                :category="modal.data"
                :categories="flatCategories"
                :saving="modal.saving"
                @save="persistCategory"
                @close="closeModal"
                @refresh="loadCategories"
            />

            <div v-if="pendingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete category</h3>
                    <p class="mt-2 text-sm text-slate-600">Deleting <span class="font-semibold text-slate-900">{{ pendingDelete.name }}</span> will detach all linked products.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyCategory">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, defineComponent, h } from 'vue';
import { catalogApi } from '@/services/admin/catalog';
import CategoryModal from './CategoryModal.vue';

const categories = ref([]);
const modal = reactive({ visible: false, data: null, saving: false });
const pendingDelete = ref(null);

const flatCategories = computed(() => {
    const list = [];
    const traverse = (nodes, depth = 0, parent = null) => {
        nodes.forEach((node) => {
            list.push({ ...node, depth, parent_id: parent?.id ?? null });
            if (node.children?.length) traverse(node.children, depth + 1, node);
        });
    };
    traverse(categories.value ?? []);
    return list;
});

onMounted(async () => {
    await loadCategories();
});

async function loadCategories() {
    const response = await catalogApi.listCategories({ tree: true });
    categories.value = response.data?.data ?? response.data ?? [];
}

function openModal(category = null) {
    modal.visible = true;
    modal.data = category;
}

function closeModal() {
    modal.visible = false;
    modal.data = null;
}

async function persistCategory(payload, pendingImageFile = null) {
    modal.saving = true;
    try {
        let category;
        if (modal.data) {
            const response = await catalogApi.updateCategory(modal.data.id, payload);
            category = response.data?.data ?? response.data;
        } else {
            const response = await catalogApi.createCategory(payload);
            category = response.data?.data ?? response.data;
            
            // If there's a pending image file, upload it now
            if (pendingImageFile && category?.id) {
                const formData = new FormData();
                formData.append('image', pendingImageFile);
                await catalogApi.uploadCategoryImage(category.id, formData);
            }
        }
        closeModal();
        await loadCategories();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save category');
    } finally {
        modal.saving = false;
    }
}

function confirmDelete(category) {
    pendingDelete.value = category;
}

async function destroyCategory() {
    if (!pendingDelete.value) return;
    try {
        await catalogApi.deleteCategory(pendingDelete.value.id);
        pendingDelete.value = null;
        await loadCategories();
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete category');
    }
}

const CategoryRow = defineComponent({
    name: 'CategoryRow',
    props: {
        node: { type: Object, required: true },
        depth: { type: Number, default: 0 },
    },
    emits: ['edit', 'delete'],
    setup(props, { emit }) {
        const indentStyle = { paddingLeft: `${props.depth * 1.5}rem` };

        const rows = [];

        rows.push(
            h('tr', { class: 'hover:bg-slate-50/75' }, [
                // Image column
                h('td', { class: 'px-4 py-4' }, [
                    props.node.image_url 
                        ? h('img', { 
                            src: props.node.image_url, 
                            alt: props.node.name,
                            class: 'h-12 w-12 rounded-lg object-cover border border-slate-200'
                        })
                        : h('div', { 
                            class: 'flex h-12 w-12 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50'
                        }, [
                            h('svg', { 
                                class: 'h-5 w-5 text-slate-400',
                                fill: 'none',
                                stroke: 'currentColor',
                                viewBox: '0 0 24 24'
                            }, [
                                h('path', {
                                    'stroke-linecap': 'round',
                                    'stroke-linejoin': 'round',
                                    'stroke-width': '2',
                                    d: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'
                                })
                            ])
                        ])
                ]),
                // Category name column
                h('td', { class: 'px-4 py-4', style: indentStyle }, [
                    h('div', [
                        h('p', { class: 'font-semibold text-slate-900' }, props.node.name),
                        props.node.description ? h('p', { class: 'text-xs text-slate-500 mt-1' }, props.node.description) : null,
                    ]),
                ]),
                // Slug column
                h('td', { class: 'px-4 py-4 text-slate-600' }, props.node.slug),
                // Status column
                h('td', { class: 'px-4 py-4' }, [
                    h('span', { 
                        class: props.node.status === 'published' 
                            ? 'inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700'
                            : props.node.status === 'draft'
                            ? 'inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600'
                            : 'inline-flex rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700'
                    }, props.node.status)
                ]),
                // Featured column
                h('td', { class: 'px-4 py-4 text-center' }, [
                    props.node.is_featured 
                        ? h('span', { 
                            class: 'inline-flex items-center justify-center rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-700',
                            title: 'Featured on homepage'
                        }, [
                            h('svg', {
                                class: 'h-3 w-3 mr-1',
                                fill: 'currentColor',
                                viewBox: '0 0 20 20'
                            }, [
                                h('path', {
                                    d: 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z'
                                })
                            ]),
                            'Featured'
                        ])
                        : h('span', { class: 'text-slate-400 text-xs' }, '—')
                ]),
                h('td', { class: 'px-4 py-4 text-right' }, [
                    h('div', { class: 'flex justify-end gap-2' }, [
                        h(
                            'button',
                            {
                                type: 'button',
                                class: 'rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600',
                                onClick: () => emit('edit', props.node),
                            },
                            'Edit'
                        ),
                        h(
                            'button',
                            {
                                type: 'button',
                                class: 'rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50',
                                onClick: () => emit('delete', props.node),
                            },
                            'Delete'
                        ),
                    ]),
                ]),
            ])
        );

        if (props.node.children?.length) {
            props.node.children.forEach((child) => {
                rows.push(
                    h(CategoryRow, {
                        key: `${child.id}-${child.updated_at}`,
                        node: child,
                        depth: props.depth + 1,
                        onEdit: (value) => emit('edit', value),
                        onDelete: (value) => emit('delete', value),
                    })
                );
            });
        }

        return () => rows;
    },
});
</script>

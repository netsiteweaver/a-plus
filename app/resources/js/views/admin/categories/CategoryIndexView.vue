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
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-left">Status</th>
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

async function persistCategory(payload) {
    modal.saving = true;
    try {
        if (modal.data) {
            await catalogApi.updateCategory(modal.data.id, payload);
        } else {
            await catalogApi.createCategory(payload);
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
                h('td', { class: 'px-4 py-4', style: indentStyle }, [
                    h('div', [
                        h('p', { class: 'font-semibold text-slate-900' }, props.node.name),
                        props.node.description ? h('p', { class: 'text-xs text-slate-500' }, props.node.description) : null,
                    ]),
                ]),
                h('td', { class: 'px-4 py-4 text-slate-600' }, props.node.slug),
                h('td', { class: 'px-4 py-4 text-slate-600' }, props.node.status),
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

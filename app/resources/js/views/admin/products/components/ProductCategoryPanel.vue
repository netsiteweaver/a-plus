<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Category placement</h2>
                <p class="text-sm text-slate-500">Assign navigation groups and choose a primary merchandising category.</p>
            </div>
            <span v-if="success" class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-600">Saved</span>
        </header>

        <div v-if="errorMessage" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ errorMessage }}
        </div>

        <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Available categories</p>
                <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 p-4">
                    <CategoryNode
                        v-for="node in categories"
                        :key="node.id"
                        :node="node"
                        :depth="0"
                        v-model:selected="selected"
                        v-model:primary="primary"
                    />
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-800">Summary</p>
                <p class="mt-2 text-xs text-slate-500">{{ selected.length }} categories selected</p>
                <p class="text-xs text-slate-500">Primary: <span class="font-medium text-slate-800">{{ primaryLabel }}</span></p>

                <button
                    type="button"
                    class="mt-4 w-full rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white shadow hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
                    :disabled="saving"
                    @click="save"
                >
                    <svg
                        v-if="saving"
                        class="mr-2 inline h-3 w-3 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5L12 3m0 0L7.5 7.5M12 3v18" />
                    </svg>
                    Apply changes
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, defineComponent, h, ref, watch, watchEffect } from 'vue';
import { catalogApi } from '@/services/admin/catalog';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const selected = ref([]);
const primary = ref(null);
const saving = ref(false);
const success = ref(false);
const errorMessage = ref('');

const categoryMap = computed(() => {
    const map = new Map();
    const traverse = (nodes) => {
        nodes.forEach((node) => {
            map.set(node.id, node);
            if (node.children?.length) {
                traverse(node.children);
            }
        });
    };
    traverse(props.categories ?? []);
    return map;
});

const primaryLabel = computed(() => {
    if (!primary.value) return 'Not set';
    return categoryMap.value.get(primary.value)?.name ?? 'Not set';
});

watchEffect(() => {
    const next = props.product;
    if (! next) {
        return;
    }

    selected.value = (next.categories ?? []).map((category) => category.id);
    primary.value = next.categories?.find((category) => category.pivot?.is_primary)?.id ?? null;
    success.value = false;
});

watch(selected, (values) => {
    if (!values.includes(primary.value)) {
        primary.value = values[0] ?? null;
    }
});

async function save() {
    saving.value = true;
    success.value = false;
    errorMessage.value = '';

    try {
        await catalogApi.updateProduct(props.product.id, {
            category_ids: selected.value,
            primary_category_id: primary.value,
        });

        success.value = true;
        emit('updated');
    } catch (error) {
        console.error('Category update error:', error);
        
        if (error.response && error.response.status === 422) {
            errorMessage.value = error.response.data.message || 'Validation failed. Please check your category selection.';
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to update categories. Please try again.';
        }
    } finally {
        saving.value = false;
    }
}

const CategoryNode = defineComponent({
    name: 'CategoryNode',
    props: {
        node: { type: Object, required: true },
        depth: { type: Number, default: 0 },
        selected: { type: Array, required: true },
        primary: { type: Number, default: null },
    },
    emits: ['update:selected', 'update:primary'],
    setup(props, { emit }) {
        const toggle = (event) => {
            const checked = event.target.checked;
            const values = new Set(props.selected);

            if (checked) {
                values.add(props.node.id);
            } else {
                values.delete(props.node.id);
            }

            emit('update:selected', Array.from(values));
        };

        const setPrimary = () => {
            emit('update:primary', props.node.id);
        };

        return () => {
            const elements = [];

            elements.push(
                h('div', { class: 'flex items-center gap-2 py-1', style: { marginLeft: `${props.depth * 1.25}rem` } }, [
                    h('input', {
                        type: 'checkbox',
                        class: 'h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500',
                        checked: props.selected.includes(props.node.id),
                        onChange: toggle,
                    }),
                    h('span', { class: 'flex-1 text-sm text-slate-700' }, props.node.name),
                    props.selected.includes(props.node.id)
                        ? h(
                              'button',
                              {
                                  type: 'button',
                                  class: ['rounded-full border px-2 py-0.5 text-xs', props.primary === props.node.id ? 'border-sky-300 bg-sky-50 text-sky-600' : 'border-slate-200 text-slate-400'],
                                  onClick: setPrimary,
                              },
                              props.primary === props.node.id ? 'Primary' : 'Set primary'
                          )
                        : null,
                ])
            );

            if (props.node.children?.length) {
                props.node.children.forEach((child) => {
                    elements.push(
                        h(CategoryNode, {
                            node: child,
                            depth: props.depth + 1,
                            selected: props.selected,
                            primary: props.primary,
                            'onUpdate:selected': (value) => emit('update:selected', value),
                            'onUpdate:primary': (value) => emit('update:primary', value),
                        })
                    );
                });
            }

            return h('div', elements);
        };
    },
});
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Options & values</h2>
                <p class="text-sm text-slate-500">Define attribute dimensions used to compose variants.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                @click="openOptionModal()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Add option
            </button>
        </header>

        <div class="space-y-4">
            <div v-if="!options.length" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                No options defined. Create at least one option (e.g., Color, Storage) to generate variants.
            </div>

            <article
                v-for="option in options"
                :key="option.id"
                class="rounded-xl border border-slate-200 p-4"
            >
                <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ option.name }}</p>
                        <p class="text-xs text-slate-500">Code {{ option.code }} · Type {{ option.input_type }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                            @click="openOptionModal(option)"
                        >
                            Edit option
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                            @click="confirmOptionDelete(option)"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Values</p>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-sky-200 hover:text-sky-600"
                        @click="openValueModal(option)"
                    >
                        Add value
                    </button>
                </div>

                <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="value in option.values ?? []"
                        :key="value.id"
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                    >
                        <p class="text-sm font-semibold text-slate-800">{{ value.display_value ?? value.value }}</p>
                        <p class="text-xs text-slate-500">Code {{ value.value }} · Position {{ value.position }}</p>
                        <div class="mt-3 flex gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-medium text-slate-500 hover:border-sky-200 hover:text-sky-600"
                                @click="openValueModal(option, value)"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-red-200 px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50"
                                @click="confirmValueDelete(option, value)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <teleport to="body">
            <OptionModal
                v-if="optionModal.visible"
                :option="optionModal.data"
                :saving="optionModal.saving"
                @save="persistOption"
                @close="closeOptionModal"
            />

            <OptionValueModal
                v-if="valueModal.visible"
                :option="valueModal.option"
                :value="valueModal.value"
                :saving="valueModal.saving"
                @save="persistValue"
                @close="closeValueModal"
            />

            <div v-if="pendingOptionDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete option</h3>
                    <p class="mt-2 text-sm text-slate-600">Deleting <span class="font-semibold text-slate-900">{{ pendingOptionDelete.name }}</span> removes all assigned values and breaks existing variant combinations.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingOptionDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyOption">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="pendingValueDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-red-600">Delete value</h3>
                    <p class="mt-2 text-sm text-slate-600">Remove <span class="font-semibold text-slate-900">{{ pendingValueDelete.value.display_value ?? pendingValueDelete.value.value }}</span> from {{ pendingValueDelete.option.name }}?</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50" @click="pendingValueDelete = null">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500" @click="destroyValue">
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
import OptionModal from './OptionModal.vue';
import OptionValueModal from './OptionValueModal.vue';

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const optionModal = reactive({ visible: false, data: null, saving: false });
const valueModal = reactive({ visible: false, option: null, value: null, saving: false });

const pendingOptionDelete = ref(null);
const pendingValueDelete = ref(null);

function openOptionModal(option = null) {
    optionModal.visible = true;
    optionModal.data = option;
}

function closeOptionModal() {
    optionModal.visible = false;
    optionModal.data = null;
}

async function persistOption(payload) {
    optionModal.saving = true;
    try {
        if (optionModal.data) {
            await catalogApi.updateOption(props.productId, optionModal.data.id, payload);
        } else {
            await catalogApi.createOption(props.productId, payload);
        }
        closeOptionModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save option');
    } finally {
        optionModal.saving = false;
    }
}

function openValueModal(option, value = null) {
    valueModal.visible = true;
    valueModal.option = option;
    valueModal.value = value;
}

function closeValueModal() {
    valueModal.visible = false;
    valueModal.option = null;
    valueModal.value = null;
}

async function persistValue(payload) {
    valueModal.saving = true;
    try {
        if (valueModal.value) {
            await catalogApi.updateOptionValue(props.productId, valueModal.option.id, valueModal.value.id, payload);
        } else {
            await catalogApi.createOptionValue(props.productId, valueModal.option.id, payload);
        }
        closeValueModal();
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to save option value');
    } finally {
        valueModal.saving = false;
    }
}

function confirmOptionDelete(option) {
    pendingOptionDelete.value = option;
}

async function destroyOption() {
    if (!pendingOptionDelete.value) return;
    try {
        await catalogApi.deleteOption(props.productId, pendingOptionDelete.value.id);
        pendingOptionDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete option');
    }
}

function confirmValueDelete(option, value) {
    pendingValueDelete.value = { option, value };
}

async function destroyValue() {
    if (!pendingValueDelete.value) return;
    try {
        await catalogApi.deleteOptionValue(props.productId, pendingValueDelete.value.option.id, pendingValueDelete.value.value.id);
        pendingValueDelete.value = null;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message ?? 'Failed to delete option value');
    }
}
</script>

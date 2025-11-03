<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div
            v-if="section"
            class="absolute inset-x-0 top-full z-40 border-b border-slate-200 bg-white/95 py-10 text-slate-700 shadow-xl shadow-sky-100/50 backdrop-blur-xl"
        >
            <div class="mx-auto flex max-w-5xl gap-10 px-6">
                <div class="grid flex-1 gap-8 md:grid-cols-3">
                    <div v-for="column in section.columns" :key="column.heading" class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600/80">{{ column.heading }}</p>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li v-for="item in column.items" :key="item.to">
                                <RouterLink
                                    :to="item.to"
                                    class="inline-flex items-center gap-1 transition hover:text-sky-700"
                                    @click.native="emitClose"
                                >
                                    <span>{{ item.label }}</span>
                                    <span class="text-xs text-slate-400">></span>
                                </RouterLink>
                            </li>
                        </ul>
                    </div>
                </div>

                <RouterLink
                    v-if="section.hero"
                    :to="section.hero.to"
                    class="hidden w-64 overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-b from-sky-50 via-white to-white shadow-xl transition hover:border-sky-400/50 md:flex"
                    @click.native="emitClose"
                >
                    <div class="flex flex-col gap-3 p-5 text-sm">
                        <span class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-600">{{ section.hero.eyebrow }}</span>
                        <p class="text-lg font-semibold text-slate-800">{{ section.hero.title }}</p>
                        <p class="text-slate-600">{{ section.hero.description }}</p>
                        <span class="inline-flex items-center gap-1 text-sky-600">
                            Shop now
                            <span class="text-xs">></span>
                        </span>
                    </div>
                    <img
                        v-if="section.hero.image"
                        :src="section.hero.image"
                        :alt="section.hero.title"
                        class="h-full w-40 object-cover object-center"
                        loading="lazy"
                    />
                </RouterLink>
            </div>
        </div>
    </transition>
</template>

<script setup>
const props = defineProps({
    section: {
        type: Object,
        required: false,
        default: null,
    },
});

const emit = defineEmits(['close']);

const emitClose = () => emit('close');
</script>

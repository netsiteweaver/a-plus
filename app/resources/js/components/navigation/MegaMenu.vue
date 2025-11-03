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
            class="absolute inset-x-0 top-full z-40 border-b border-white/10 bg-slate-900/95 py-10 text-slate-100 shadow-2xl shadow-emerald-900/20 backdrop-blur-xl"
        >
            <div class="mx-auto flex max-w-5xl gap-10 px-6">
                <div class="grid flex-1 gap-8 md:grid-cols-3">
                    <div v-for="column in section.columns" :key="column.heading" class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">{{ column.heading }}</p>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li v-for="item in column.items" :key="item.to">
                                <RouterLink
                                    :to="item.to"
                                    class="inline-flex items-center gap-1 transition hover:text-white"
                                    @click.native="emitClose"
                                >
                                    <span>{{ item.label }}</span>
                                    <span class="text-xs text-white/30">></span>
                                </RouterLink>
                            </li>
                        </ul>
                    </div>
                </div>

                <RouterLink
                    v-if="section.hero"
                    :to="section.hero.to"
                    class="hidden w-64 overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-b from-emerald-500/10 via-slate-900 to-slate-950 shadow-xl transition hover:border-emerald-400/40 md:flex"
                    @click.native="emitClose"
                >
                    <div class="flex flex-col gap-3 p-5 text-sm">
                        <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-300">{{ section.hero.eyebrow }}</span>
                        <p class="text-lg font-semibold text-white">{{ section.hero.title }}</p>
                        <p class="text-white/60">{{ section.hero.description }}</p>
                        <span class="inline-flex items-center gap-1 text-emerald-300">
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

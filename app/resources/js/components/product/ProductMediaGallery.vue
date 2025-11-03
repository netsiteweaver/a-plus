<template>
    <div class="flex flex-col gap-4">
        <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900/60">
            <img :src="activeMedia" :alt="`Gallery media ${activeIndex + 1}`" class="w-full object-cover" />
        </div>
        <div class="grid grid-cols-3 gap-3">
            <button
                v-for="(media, index) in mediaItems"
                :key="media"
                class="overflow-hidden rounded-2xl border border-white/10 transition hover:border-emerald-400/60"
                :class="index === activeIndex ? 'border-emerald-400/60' : ''"
                @click="activeIndex = index"
                type="button"
            >
                <img :src="media" :alt="`Media thumbnail ${index + 1}`" class="h-20 w-full object-cover" loading="lazy" />
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    media: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const activeIndex = ref(0);

const mediaItems = computed(() => props.media.filter(Boolean));
const activeMedia = computed(() => mediaItems.value[activeIndex.value] ?? mediaItems.value[0]);
</script>

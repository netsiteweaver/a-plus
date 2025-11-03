<template>
    <div class="flex flex-col gap-4">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
            <img :src="activeMedia?.url" :alt="activeMedia?.alt ?? `Gallery media ${activeIndex + 1}`" class="w-full object-cover" />
        </div>
        <div class="grid grid-cols-3 gap-3">
            <button
                v-for="(media, index) in mediaItems"
                :key="media.url ?? media"
                class="overflow-hidden rounded-2xl border border-slate-200 transition hover:border-sky-400/60"
                :class="index === activeIndex ? 'border-sky-500/60 ring-2 ring-sky-100' : ''"
                @click="activeIndex = index"
                type="button"
            >
                <img
                    :src="media.thumbnail ?? media.url"
                    :alt="media.alt ?? `Media thumbnail ${index + 1}`"
                    class="h-20 w-full object-cover"
                    loading="lazy"
                />
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    mediaItems: {
        type: Array,
        default: () => [],
    },
});

const activeIndex = ref(0);

const mediaItems = computed(() =>
    props.mediaItems
        .map((item) => (typeof item === 'string' ? { url: item } : item))
        .filter((item) => Boolean(item?.url))
);

const activeMedia = computed(() => mediaItems.value[activeIndex.value] ?? mediaItems.value[0]);
</script>

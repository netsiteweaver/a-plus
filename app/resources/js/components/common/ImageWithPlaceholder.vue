<template>
    <div :class="containerClass" class="relative overflow-hidden">
        <!-- Actual image -->
        <img
            v-if="shouldShowImage"
            :src="src"
            :alt="alt"
            :class="imageClass"
            :loading="loading"
            @error="handleError"
            @load="handleLoad"
        />
        
        <!-- Placeholder -->
        <div 
            v-else
            :class="placeholderClass" 
            class="flex h-full w-full items-center justify-center bg-slate-100"
        >
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                :class="iconClass"
                class="text-slate-300" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="1.5" 
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" 
                />
            </svg>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        default: 'Image',
    },
    containerClass: {
        type: String,
        default: '',
    },
    imageClass: {
        type: String,
        default: '',
    },
    placeholderClass: {
        type: String,
        default: '',
    },
    iconClass: {
        type: String,
        default: 'h-16 w-16',
    },
    loading: {
        type: String,
        default: 'lazy',
        validator: (value) => ['lazy', 'eager'].includes(value),
    },
});

const hasError = ref(false);
const isLoaded = ref(false);

const shouldShowImage = computed(() => {
    return props.src && !hasError.value;
});

const handleError = () => {
    hasError.value = true;
};

const handleLoad = () => {
    isLoaded.value = true;
    hasError.value = false;
};

// Reset error state when src changes
const resetError = () => {
    hasError.value = false;
    isLoaded.value = false;
};

// Watch for src changes
import { watch } from 'vue';
watch(() => props.src, resetError);
</script>


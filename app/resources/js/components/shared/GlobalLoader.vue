<template>
    <Transition name="fade">
        <div v-if="loading.isLoading" class="global-loader">
            <div class="loader-overlay"></div>
            <div class="loader-content">
                <div class="loader-spinner">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                </div>
                <p class="loader-text">Loading...</p>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { useLoadingStore } from '@/stores/loading';

const loading = useLoadingStore();
</script>

<style scoped>
.global-loader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
}

.loader-content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.loader-spinner {
    position: relative;
    width: 64px;
    height: 64px;
}

.spinner-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top-color: #0ea5e9;
    border-radius: 50%;
    animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
}

.spinner-ring:nth-child(2) {
    border-top-color: #38bdf8;
    animation-delay: -0.3s;
    width: 85%;
    height: 85%;
    top: 7.5%;
    left: 7.5%;
}

.spinner-ring:nth-child(3) {
    border-top-color: #7dd3fc;
    animation-delay: -0.6s;
    width: 70%;
    height: 70%;
    top: 15%;
    left: 15%;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loader-text {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    letter-spacing: 0.025em;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-active .loader-content {
    animation: scaleIn 0.2s ease;
}

@keyframes scaleIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>


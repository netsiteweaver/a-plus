import { defineStore } from 'pinia';

export const useLoadingStore = defineStore('loading', {
    state: () => ({
        activeRequests: 0,
        isLoading: false,
    }),
    actions: {
        startLoading() {
            this.activeRequests++;
            this.isLoading = true;
        },
        finishLoading() {
            this.activeRequests = Math.max(0, this.activeRequests - 1);
            if (this.activeRequests === 0) {
                this.isLoading = false;
            }
        },
        reset() {
            this.activeRequests = 0;
            this.isLoading = false;
        },
    },
});


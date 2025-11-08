import { defineStore } from 'pinia';
import { api, ensureCsrfCookie, onUnauthorized } from '@/services/http';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        status: 'idle',
        initialized: false,
        error: null,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.user),
        roles: (state) => (state.user?.roles ?? []),
        permissions: (state) => (state.user?.permissions ?? []),
        hasRole: (state) => (role) => state.user?.roles?.includes(role) ?? false,
        hasPermission: (state) => (permission) => state.user?.permissions?.includes(permission) ?? false,
        displayName: (state) => state.user?.name ?? state.user?.email ?? '',
    },
    actions: {
        async ensureSession() {
            if (this.initialized) {
                return;
            }

            await this.fetchUser();
        },
        async fetchUser() {
            this.status = 'loading';
            this.error = null;

            try {
                const response = await api.get('/user');
                this.user = response.data?.data ?? response.data;
            } catch (error) {
                this.user = null;
            } finally {
                this.status = 'idle';
                this.initialized = true;
            }
        },
        async login(credentials) {
            this.status = 'loading';
            this.error = null;

            try {
                await ensureCsrfCookie();
                await api.post('/login', credentials);
                await this.fetchUser();
            } catch (error) {
                this.error = error.response?.data?.message ?? 'Unable to sign in. Please check your credentials.';
                this.user = null;
                throw error;
            } finally {
                this.status = 'idle';
            }
        },
        async logout() {
            try {
                await api.post('/logout');
            } catch (error) {
                console.warn('Failed to log out', error);
            } finally {
                this.reset();
            }
        },
        reset() {
            this.user = null;
            this.status = 'idle';
            this.error = null;
            this.initialized = true;
        },
    },
});

onUnauthorized(() => {
    const auth = useAuthStore();
    auth.reset();
});

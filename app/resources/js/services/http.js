import axios from 'axios';
import { useLoadingStore } from '@/stores/loading';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
});

const unauthorizedListeners = new Set();

// Request interceptor to track loading state
api.interceptors.request.use(
    (config) => {
        // Don't show loader for certain endpoints
        const skipLoader = config.skipLoader || 
                          config.url?.includes('/sanctum/csrf-cookie') ||
                          config.url?.includes('/config/');
        
        if (!skipLoader) {
            const loadingStore = useLoadingStore();
            loadingStore.startLoading();
        }
        
        return config;
    },
    (error) => {
        const loadingStore = useLoadingStore();
        loadingStore.finishLoading();
        return Promise.reject(error);
    }
);

// Response interceptor to finish loading state
api.interceptors.response.use(
    (response) => {
        const skipLoader = response.config.skipLoader || 
                          response.config.url?.includes('/sanctum/csrf-cookie') ||
                          response.config.url?.includes('/config/');
        
        if (!skipLoader) {
            const loadingStore = useLoadingStore();
            loadingStore.finishLoading();
        }
        
        return response;
    },
    (error) => {
        const skipLoader = error.config?.skipLoader || 
                          error.config?.url?.includes('/sanctum/csrf-cookie') ||
                          error.config?.url?.includes('/config/');
        
        if (!skipLoader) {
            const loadingStore = useLoadingStore();
            loadingStore.finishLoading();
        }
        
        if (error.response?.status === 401) {
            unauthorizedListeners.forEach((listener) => listener());
        }

        return Promise.reject(error);
    }
);

export function onUnauthorized(callback) {
    unauthorizedListeners.add(callback);

    return () => unauthorizedListeners.delete(callback);
}

export async function ensureCsrfCookie() {
    try {
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
        });
    } catch (error) {
        console.error('Failed to initialize CSRF cookie', error);
        throw error;
    }
}

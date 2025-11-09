import axios from 'axios';
import { useLoadingStore } from '@/stores/loading';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const unauthorizedListeners = new Set();
let csrfInitialized = false;

// Request interceptor to ensure CSRF token and track loading state
api.interceptors.request.use(
    async (config) => {
        // Ensure CSRF cookie for state-changing requests
        if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase()) && !csrfInitialized) {
            try {
                await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                csrfInitialized = true;
            } catch (error) {
                console.error('Failed to fetch CSRF cookie', error);
            }
        }

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

// Response interceptor to handle loading state, 401, and 419 errors
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
    async (error) => {
        const skipLoader = error.config?.skipLoader || 
                          error.config?.url?.includes('/sanctum/csrf-cookie') ||
                          error.config?.url?.includes('/config/');
        
        if (!skipLoader) {
            const loadingStore = useLoadingStore();
            loadingStore.finishLoading();
        }
        
        // Handle CSRF token mismatch - retry once
        if (error.response?.status === 419) {
            csrfInitialized = false;
            const config = error.config;
            
            if (!config._retry) {
                config._retry = true;
                try {
                    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                    csrfInitialized = true;
                    return api.request(config);
                } catch (retryError) {
                    return Promise.reject(retryError);
                }
            }
        }
        
        // Handle unauthorized
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
        csrfInitialized = true;
    } catch (error) {
        console.error('Failed to initialize CSRF cookie', error);
        throw error;
    }
}

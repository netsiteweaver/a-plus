import axios from 'axios';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
});

const unauthorizedListeners = new Set();

api.interceptors.response.use(
    (response) => response,
    (error) => {
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

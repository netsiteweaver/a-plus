import { useLoadingStore } from '@/stores/loading';

/**
 * Composable for managing loading states
 * 
 * @example
 * // Automatic loading with API interceptors
 * await api.get('/admin/products'); // Shows loader automatically
 * 
 * @example
 * // Skip loader for specific request
 * await api.get('/admin/products', { skipLoader: true });
 * 
 * @example
 * // Manual loading control
 * const { showLoader, hideLoader, withLoader } = useLoader();
 * 
 * showLoader();
 * // do something
 * hideLoader();
 * 
 * // Or use withLoader wrapper
 * await withLoader(async () => {
 *   // your async operation
 * });
 */
export function useLoader() {
    const loadingStore = useLoadingStore();

    const showLoader = () => {
        loadingStore.startLoading();
    };

    const hideLoader = () => {
        loadingStore.finishLoading();
    };

    const withLoader = async (callback) => {
        try {
            showLoader();
            return await callback();
        } finally {
            hideLoader();
        }
    };

    return {
        showLoader,
        hideLoader,
        withLoader,
        isLoading: loadingStore.isLoading,
    };
}


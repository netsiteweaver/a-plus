import { computed } from 'vue';
import { useSettings } from './useSettings';

/**
 * Composable for currency formatting using site-wide settings
 */
export function useCurrency() {
    const { currency, currencySymbol } = useSettings();

    /**
     * Format a number as currency using the site's currency settings
     * @param {number} value - The numeric value to format
     * @param {Object} options - Optional Intl.NumberFormat options to override defaults
     * @returns {string} Formatted currency string
     */
    const formatCurrency = (value, options = {}) => {
        const defaultOptions = {
            style: 'currency',
            currency: currency.value || 'MUR',
            maximumFractionDigits: 0,
        };

        const formatOptions = { ...defaultOptions, ...options };

        try {
            return new Intl.NumberFormat('en-US', formatOptions).format(value ?? 0);
        } catch (error) {
            // Fallback if currency code is invalid
            console.warn(`Invalid currency code: ${currency.value}`, error);
            return `${currencySymbol.value || 'Rs'}${(value ?? 0).toFixed(0)}`;
        }
    };

    /**
     * Format a price range (e.g., "$100 - $200")
     * @param {number} minPrice - Minimum price
     * @param {number} maxPrice - Maximum price
     * @param {Object} options - Optional formatting options
     * @returns {string} Formatted price range
     */
    const formatPriceRange = (minPrice, maxPrice, options = {}) => {
        if (minPrice === maxPrice) {
            return formatCurrency(minPrice, options);
        }
        return `${formatCurrency(minPrice, options)} - ${formatCurrency(maxPrice, options)}`;
    };

    /**
     * Calculate and format discount percentage
     * @param {number} originalPrice - Original price
     * @param {number} salePrice - Sale price
     * @returns {string} Formatted discount percentage (e.g., "-25%")
     */
    const formatDiscount = (originalPrice, salePrice) => {
        if (!originalPrice || !salePrice || originalPrice <= salePrice) {
            return '';
        }
        const discount = Math.round(((originalPrice - salePrice) / originalPrice) * 100);
        return `-${discount}%`;
    };

    return {
        currency: computed(() => currency.value),
        currencySymbol: computed(() => currencySymbol.value),
        formatCurrency,
        formatPriceRange,
        formatDiscount,
    };
}


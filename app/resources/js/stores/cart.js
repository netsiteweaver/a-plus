import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    // State
    const items = ref([]);

    // Getters
    const itemCount = computed(() => {
        return items.value.reduce((total, item) => total + item.quantity, 0);
    });

    const totalAmount = computed(() => {
        return items.value.reduce((total, item) => total + (item.price * item.quantity), 0);
    });

    // Actions
    function addItem(product, quantity = 1) {
        const existingItem = items.value.find(item => item.id === product.id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            items.value.push({
                id: product.id,
                slug: product.slug,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: quantity,
            });
        }

        // Store in localStorage for persistence
        saveCart();
    }

    function removeItem(productId) {
        const index = items.value.findIndex(item => item.id === productId);
        if (index > -1) {
            items.value.splice(index, 1);
            saveCart();
        }
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, quantity);
            saveCart();
        }
    }

    function clearCart() {
        items.value = [];
        saveCart();
    }

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(items.value));
    }

    function loadCart() {
        const saved = localStorage.getItem('cart');
        if (saved) {
            try {
                items.value = JSON.parse(saved);
            } catch (error) {
                console.error('Failed to load cart:', error);
                items.value = [];
            }
        }
    }

    // Initialize cart from localStorage
    loadCart();

    return {
        items,
        itemCount,
        totalAmount,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        loadCart,
    };
});


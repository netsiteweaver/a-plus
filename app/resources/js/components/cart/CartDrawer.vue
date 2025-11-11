<template>
    <transition name="drawer">
        <div
            v-if="ui.isCartDrawerOpen"
            class="fixed inset-0 z-50 overflow-hidden"
            @click="ui.toggleCartDrawer(false)"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <!-- Drawer -->
            <div
                class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-white shadow-2xl"
                @click.stop
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-bold text-slate-800">Shopping Cart</h2>
                    <button
                        @click="ui.toggleCartDrawer(false)"
                        class="rounded-full p-2 transition hover:bg-slate-100"
                        aria-label="Close cart"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cart Items -->
                <div v-if="cartStore.items.length > 0" class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="space-y-4">
                        <div
                            v-for="item in cartStore.items"
                            :key="item.id"
                            class="flex gap-4 rounded-lg border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:shadow-md"
                        >
                            <RouterLink 
                                :to="`/product/${item.slug}`"
                                @click="ui.toggleCartDrawer(false)"
                                class="shrink-0"
                            >
                                <img
                                    :src="item.image"
                                    :alt="item.name"
                                    class="h-20 w-20 rounded-lg object-cover"
                                />
                            </RouterLink>
                            <div class="flex flex-1 flex-col justify-between">
                                <div>
                                    <RouterLink 
                                        :to="`/product/${item.slug}`"
                                        @click="ui.toggleCartDrawer(false)"
                                        class="text-sm font-semibold text-slate-800 transition hover:text-blue-600"
                                    >
                                        {{ item.name }}
                                    </RouterLink>
                                    <p class="mt-1 text-sm font-bold text-slate-900">
                                        {{ formatCurrency(item.price) }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="decrementQuantity(item)"
                                            class="flex h-7 w-7 items-center justify-center rounded border border-slate-300 text-slate-600 transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
                                        >
                                            -
                                        </button>
                                        <span class="w-8 text-center text-sm font-semibold">{{ item.quantity }}</span>
                                        <button
                                            @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                                            class="flex h-7 w-7 items-center justify-center rounded border border-slate-300 text-slate-600 transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
                                        >
                                            +
                                        </button>
                                    </div>
                                    <button
                                        @click="cartStore.removeItem(item.id)"
                                        class="text-xs text-red-500 transition hover:text-red-700"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-1 flex-col items-center justify-center px-6 py-12 text-center">
                    <svg class="mb-4 h-20 w-20 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-700">Your cart is empty</h3>
                    <p class="mt-2 text-sm text-slate-500">Add some products to get started!</p>
                    <button
                        @click="ui.toggleCartDrawer(false)"
                        class="mt-6 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-2 text-sm font-semibold uppercase tracking-[0.28em] text-white transition hover:from-blue-600 hover:to-blue-700"
                    >
                        Continue Shopping
                    </button>
                </div>

                <!-- Footer with Total and Checkout -->
                <div v-if="cartStore.items.length > 0" class="border-t border-slate-200 px-6 py-4">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-600">Total</span>
                        <span class="text-2xl font-bold text-slate-900">{{ formatCurrency(cartStore.totalAmount) }}</span>
                    </div>
                    <button
                        @click="handleCheckout"
                        class="w-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3 font-bold uppercase tracking-[0.28em] text-white shadow-lg shadow-blue-500/30 transition-all hover:from-blue-600 hover:to-blue-700 hover:shadow-xl"
                    >
                        Checkout
                    </button>
                    <button
                        @click="ui.toggleCartDrawer(false)"
                        class="mt-2 w-full rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-600 transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
                    >
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { useUiStore } from '@/stores/ui';
import { useCartStore } from '@/stores/cart';
import { useCurrency } from '@/composables/useCurrency';

const ui = useUiStore();
const cartStore = useCartStore();
const { formatCurrency } = useCurrency();

const decrementQuantity = (item) => {
    if (item.quantity > 1) {
        cartStore.updateQuantity(item.id, item.quantity - 1);
    } else {
        cartStore.removeItem(item.id);
    }
};

const handleCheckout = () => {
    // TODO: Implement checkout functionality
    alert('Checkout functionality coming soon!');
};
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.3s ease;
}

.drawer-enter-active .absolute,
.drawer-leave-active .absolute {
    transition: transform 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.drawer-enter-from .absolute:last-child,
.drawer-leave-to .absolute:last-child {
    transform: translateX(100%);
}
</style>


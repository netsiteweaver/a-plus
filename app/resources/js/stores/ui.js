import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
    state: () => ({
        isMobileMenuOpen: false,
        activeMegaMenu: null,
        isCartDrawerOpen: false,
    }),
    actions: {
        toggleMobileMenu(force) {
            this.isMobileMenuOpen = typeof force === 'boolean' ? force : !this.isMobileMenuOpen;
        },
        setActiveMegaMenu(section) {
            this.activeMegaMenu = section;
        },
        closeMegaMenu() {
            this.activeMegaMenu = null;
        },
        toggleCartDrawer(force) {
            this.isCartDrawerOpen = typeof force === 'boolean' ? force : !this.isCartDrawerOpen;
        },
    },
});

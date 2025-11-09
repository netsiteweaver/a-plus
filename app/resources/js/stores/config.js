import { defineStore } from 'pinia';
import axios from 'axios';

export const useConfigStore = defineStore('config', {
    state: () => ({
        settings: {},
        navigation: {
            primary: null,
            utility: null,
            footer: null,
        },
        contentBlocks: {},
        loading: {
            settings: false,
            navigation: false,
            content: false,
        },
        loaded: {
            settings: false,
            navigation: {},
            content: {},
        },
    }),

    getters: {
        // Company Info
        companyName: (state) => state.settings.company?.website_name || 'A Plus Technology',
        companyTagline: (state) => state.settings.company?.tagline || '',
        companyDescription: (state) => state.settings.company?.description || '',
        companyLogo: (state) => state.settings.company?.logo_url || '/images/logo.png',
        companyFavicon: (state) => state.settings.company?.favicon_url || '/favicon.ico',

        // Legal Info
        legalName: (state) => state.settings.legal?.legal_name || '',
        legalAddress: (state) => state.settings.legal?.legal_address || {},

        // Business Settings
        currency: (state) => state.settings.business?.currency || 'MUR',
        currencySymbol: (state) => state.settings.business?.currency_symbol || 'Rs',
        timezone: (state) => state.settings.business?.timezone || 'UTC',
        businessEmail: (state) => state.settings.business?.email || '',
        businessPhone: (state) => state.settings.business?.phone_number || '',
        supportEmail: (state) => state.settings.business?.support_email || '',
        supportPhone: (state) => state.settings.business?.support_phone || '',

        // Contact Info
        primaryAddress: (state) => state.settings.contact?.primary_address || {},
        businessHours: (state) => state.settings.contact?.business_hours || {},

        // Social Media
        socialLinks: (state) => state.settings.social || {},

        // Feature Flags
        isFeatureEnabled: (state) => (feature) => {
            return state.settings.features?.[`enable_${feature}`] ?? true;
        },

        // Branding
        primaryColor: (state) => state.settings.branding?.primary_color || '#0ea5e9',
        secondaryColor: (state) => state.settings.branding?.secondary_color || '#64748b',
        showTagline: (state) => state.settings.branding?.show_tagline ?? true,
        showPromoBanner: (state) => state.settings.branding?.show_promo_banner ?? true,
        promoBannerText: (state) => state.settings.branding?.promo_banner_text || '',

        // Navigation
        primaryNavigation: (state) => {
            const items = state.navigation.primary?.items || [];
            console.log('[Navigation Getter] primaryNavigation:', items.length, 'items');
            return items;
        },
        utilityNavigation: (state) => state.navigation.utility?.items || [],
        footerNavigation: (state) => state.navigation.footer?.items || [],

        // Content
        getContentBlocks: (state) => (page) => state.contentBlocks[page] || [],
    },

    actions: {
        async fetchSettings() {
            if (this.loaded.settings) return;
            
            this.loading.settings = true;
            try {
                const { data } = await axios.get('/api/config/settings');
                this.settings = data;
                this.loaded.settings = true;
                this.applyTheme();
            } catch (error) {
                console.error('Failed to load settings:', error);
            } finally {
                this.loading.settings = false;
            }
        },

        async fetchNavigation(location) {
            if (this.loaded.navigation[location]) return;

            this.loading.navigation = true;
            try {
                const { data } = await axios.get(`/api/config/navigation/${location}`);
                console.log(`[Navigation] Loaded ${location}:`, data);
                console.log(`[Navigation] Items count:`, data.items?.length || 0);
                this.navigation[location] = data;
                this.loaded.navigation[location] = true;
            } catch (error) {
                console.error(`Failed to load ${location} navigation:`, error);
            } finally {
                this.loading.navigation = false;
            }
        },

        async fetchContentBlocks(page) {
            if (this.loaded.content[page]) return;

            this.loading.content = true;
            try {
                const { data } = await axios.get(`/api/config/content/${page}`);
                this.contentBlocks[page] = data;
                this.loaded.content[page] = true;
            } catch (error) {
                console.error(`Failed to load content for ${page}:`, error);
            } finally {
                this.loading.content = false;
            }
        },

        applyTheme() {
            if (!this.settings.branding) return;

            const root = document.documentElement;
            
            // Apply CSS custom properties
            if (this.settings.branding.primary_color) {
                root.style.setProperty('--color-primary', this.settings.branding.primary_color);
            }
            if (this.settings.branding.secondary_color) {
                root.style.setProperty('--color-secondary', this.settings.branding.secondary_color);
            }

            // Apply favicon if configured
            if (this.settings.company?.favicon_url) {
                const link = document.querySelector("link[rel~='icon']") || document.createElement('link');
                link.rel = 'icon';
                link.href = this.settings.company.favicon_url;
                document.head.appendChild(link);
            }

            // Set page title
            if (this.settings.company?.website_name) {
                document.title = this.settings.company.website_name;
            }
        },

        async initializeApp() {
            await Promise.all([
                this.fetchSettings(),
                this.fetchNavigation('primary'),
                this.fetchNavigation('utility'),
                this.fetchNavigation('footer'),
            ]);
        },
    },
});


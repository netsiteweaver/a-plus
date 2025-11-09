import { computed } from 'vue';
import { useConfigStore } from '@/stores/config';

export function useSettings() {
    const config = useConfigStore();

    return {
        // Company Info
        companyName: computed(() => config.companyName),
        companyTagline: computed(() => config.companyTagline),
        companyDescription: computed(() => config.companyDescription),
        companyLogo: computed(() => config.companyLogo),
        
        // Legal Info
        legalName: computed(() => config.legalName),
        legalAddress: computed(() => config.legalAddress),
        
        // Business Settings
        currency: computed(() => config.currency),
        currencySymbol: computed(() => config.currencySymbol),
        timezone: computed(() => config.timezone),
        businessEmail: computed(() => config.businessEmail),
        businessPhone: computed(() => config.businessPhone),
        supportEmail: computed(() => config.supportEmail),
        supportPhone: computed(() => config.supportPhone),
        
        // Contact Info
        primaryAddress: computed(() => config.primaryAddress),
        businessHours: computed(() => config.businessHours),
        
        // Social Media
        socialLinks: computed(() => config.socialLinks),
        
        // Branding
        primaryColor: computed(() => config.primaryColor),
        secondaryColor: computed(() => config.secondaryColor),
        showTagline: computed(() => config.showTagline),
        showPromoBanner: computed(() => config.showPromoBanner),
        promoBannerText: computed(() => config.promoBannerText),
        
        // Feature Flags
        isFeatureEnabled: (feature) => config.isFeatureEnabled(feature),
        
        // Navigation
        primaryNavigation: computed(() => config.primaryNavigation),
        utilityNavigation: computed(() => config.utilityNavigation),
        footerNavigation: computed(() => config.footerNavigation),
        
        // Content
        getContentBlocks: (page) => computed(() => config.getContentBlocks(page)),
        
        // State
        loading: computed(() => config.loading),
        
        // Actions
        fetchContentBlocks: (page) => config.fetchContentBlocks(page),
    };
}


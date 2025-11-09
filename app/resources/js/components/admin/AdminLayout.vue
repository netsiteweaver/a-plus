<template>
    <div class="flex min-h-screen bg-slate-100 text-slate-900">
        <AdminSidebar 
            :collapsed="isSidebarCollapsed" 
            :is-mobile-menu-open="isMobileMenuOpen"
            @toggle="toggleSidebar"
            @close-mobile-menu="closeMobileMenu"
        />

        <div class="flex flex-1 flex-col">
            <AdminTopbar
                :collapsed="isSidebarCollapsed"
                @toggle-sidebar="toggleMobileMenu"
            />

            <main class="relative flex-1 overflow-y-auto p-6 lg:p-10">
                <Breadcrumbs v-if="breadcrumbs.length" :items="breadcrumbs" class="mb-6" />
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import AdminSidebar from './AdminSidebar.vue';
import AdminTopbar from './AdminTopbar.vue';
import Breadcrumbs from '@/components/common/Breadcrumbs.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const isSidebarCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const { breadcrumbs } = useBreadcrumbs();

function toggleSidebar(force) {
    if (typeof force === 'boolean') {
        isSidebarCollapsed.value = force;
        return;
    }

    isSidebarCollapsed.value = !isSidebarCollapsed.value;
}

function toggleMobileMenu() {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function closeMobileMenu() {
    isMobileMenuOpen.value = false;
}
</script>

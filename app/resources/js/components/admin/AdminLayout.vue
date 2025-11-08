<template>
    <div class="flex min-h-screen bg-slate-100 text-slate-900">
        <AdminSidebar :collapsed="isSidebarCollapsed" @toggle="toggleSidebar" />

        <div class="flex flex-1 flex-col">
            <AdminTopbar
                :collapsed="isSidebarCollapsed"
                @toggle-sidebar="toggleSidebar"
            />

            <main class="relative flex-1 overflow-y-auto p-6 lg:p-10">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import AdminSidebar from './AdminSidebar.vue';
import AdminTopbar from './AdminTopbar.vue';

const isSidebarCollapsed = ref(false);

function toggleSidebar(force) {
    if (typeof force === 'boolean') {
        isSidebarCollapsed.value = force;
        return;
    }

    isSidebarCollapsed.value = !isSidebarCollapsed.value;
}
</script>

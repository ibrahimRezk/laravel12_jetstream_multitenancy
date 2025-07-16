<script setup lang="ts">
import AppContent from '@/components2/AppContent.vue';
import AppShell from '@/components2/AppShell.vue';
import AppSidebar from '@/components2/AppSidebar.vue';
import AppSidebarHeader from '@/components2/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>

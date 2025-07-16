<script setup pages>
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
// import type { BreadcrumbItemType } from '@/types';

// interface Props {
//     breadcrumbs;
// }

    defineProps ({
    breadcrumbs:  Array,
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>

<script setup pages>
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { onMounted, ref } from "vue";

// import type { BreadcrumbItemType } from '@/types';

// interface Props {
//     breadcrumbs;
// }

// withDefaults(defineProps(), {
//     breadcrumbs: () => [],
// });

// defineProps({
//     breadcrumbs: () => [],
// });






    // onMounted(() => {
    //     // On page load or when changing themes, best to add inline in `head` to avoid FOUC
    //     if (
    //         !("theme" in localStorage) &&
    //         window.matchMedia("(prefers-color-scheme: dark)").matches
    //     ) {
    //         document.documentElement.classList.add("dark");
    //         // appearance.value = "system";
    //     } else if (
    //         !("theme" in localStorage) &&
    //         !window.matchMedia("(prefers-color-scheme: dark)").matches
    //     ) {
    //         document.documentElement.classList.remove("dark");
    //         // appearance.value = "system";
    //     } else if (
    //         localStorage.theme === "dark" ||
    //         (!("theme" in localStorage) &&
    //             window.matchMedia("(prefers-color-scheme: dark)").matches)
    //     ) {
    //         document.documentElement.classList.add("dark");
    //         // appearance.value = "dark";
    //     } else {
    //         document.documentElement.classList.remove("dark");
    //         // appearance.value = "light";
    //     }
    // });


    defineProps ({
    breadcrumbs: Array,
});

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, Head, router } from "@inertiajs/vue3";
import PlaceholderPattern from "../components/PlaceholderPattern.vue";
import { useSubscription } from "@/composables/useSubscription";
import Button from "@/components/ui/button/Button.vue";
import { onMounted, ref, watch } from "vue";
import axios from "axios";



const breadcrumbs = [
    {
        title: "Dashboard",
        href: "/dashboard",
    },
];
// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: 'Dashboard',
//         href: '/dashboard',
//     },
// ];

// const cancelSubscription = () => {
//     router.delete(route("tenant.cancelSubscription", {}), {
//         preserveScroll: true,
//         preserveState: true,
//         onBefore: () => {
//             // isDeleting.value = true;
//         },
//         onSuccess: () => {
//             // deleteModal.value = false;
//             // itemToDelete.value = [];
//             // ids.value = [];
//             // deleteMultipleItems.value = false;
//         },
//         onFinish: () => {
//             // isDeleting.value = false;
//             // usePage().props.menus.forEach((menu) => {
//             //     menu.isActive
//             //         ? (menu.open = true)
//             //         : (menu.open = false);
//             // });
//         },
//     });
// };
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                     

                  
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

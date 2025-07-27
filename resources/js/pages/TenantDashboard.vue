<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, Head, router } from "@inertiajs/vue3";
import PlaceholderPattern from "../components/PlaceholderPattern.vue";
import { useSubscription } from "@/composables/useSubscription";
import Button from "@/components/ui/button/Button.vue";
import { onMounted, ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
    tenantSubscription : Object ,
})

const {
    fetchPlans,
    // currentActiveSubscription,
    fetchTenantSubscription,
    cancelSubscription,
} = useSubscription();



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
                    <!-- <PlaceholderPattern /> -->
                     

                    <!-- :href="route('tenant.plans')" -->
                    <Button variant="outline" v-show="!props.tenantSubscription"
                    @click="fetchPlans('tenant')"
                    class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent hover:cursor-pointer"
                    >
                    choose a paln <span class=" text-red-500">..........(only view if no subscription)</span>
                </Button>
                <!-- :href="route('tenant.getTenantSubscription')" -->
                <Button v-show="props.tenantSubscription" variant="outline" 
                @click="fetchTenantSubscription('tenant' ,props.tenantSubscription?.tenant_id )"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent hover:cursor-pointer"
                    >
                        view subscriptions <span class=" text-green-500">............. (only view if subscription exists)</span>
                    </Button>
                     <!-- <Link v-show="currentActiveSubscription"
                        :href="route('tenant.getTenantSubscription')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        view subscriptions <span class=" text-green-500">............. (only view if subscription exists)</span>
                    </Link> -->


                    <Link v-show="props.tenantSubscription"
                        :href="
                            route('tenant.plans', {
                                type: 'changePlan',
                            })
                        "
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        change paln <span class=" text-green-500">............. (only view if subscription exists)</span>
                    </Link>

                    <Link
                        :href="route('tenant.addUser')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        add tenant user <span class=" text-orange-500">........(add random user)</span>
                    </Link>

                    <form v-show="props.tenantSubscription"
                        @submit.prevent="cancelSubscription('tenant' , props.tenantSubscription?.tenant_id)"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        <button type="submit" class="hover:cursor-pointer">
                            cancel subscription <span class=" text-green-500">............. (only view if subscription exists)</span>
                        </button>
                    </form>
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

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, Head, router } from "@inertiajs/vue3";
import PlaceholderPattern from "../components/PlaceholderPattern.vue";

import { useSubscription } from "@/composables/useSubscription";
import Button from "@/components/ui/button/Button.vue";



const {
    fetchPlans,
    fetchTenants,
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

const cancelSubscription = ()=>{
    router.delete(
            route('admin.cancelSubscription', {}),
            {
                preserveScroll: true,
                preserveState: true,
                onBefore: () => {
                    // isDeleting.value = true;
                },
                onSuccess: () => {
                    // deleteModal.value = false;
                    // itemToDelete.value = [];
                    // ids.value = [];
                    // deleteMultipleItems.value = false;

                },
                onFinish: () => {
                    // isDeleting.value = false;
                    // usePage().props.menus.forEach((menu) => {
                    //     menu.isActive
                    //         ? (menu.open = true)
                    //         : (menu.open = false);
                    // });
                },
            }
        );
}
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

                    <!-- <Link
                        :href="route('admin.getTenants')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        view all tenants
                    </Link> -->
                   <Button variant="outline"
                    @click="fetchTenants()"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent hover:cursor-pointer"
                    >
                        view all tenants
                    </Button>
                   <Button variant="outline"
                    @click="fetchPlans('admin')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent hover:cursor-pointer"
                    >
                        view plans
                    </Button>
                    <!-- <Link
                        :href="route('admin.plan')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        choose a paln
                    </Link>
                    <Link
                        :href="route('admin.getTenantSubscription')"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        view subscriptions 
                    </Link>
                    <Link
                        :href="route('admin.plan' , {type:'changePlan'})"
                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    >
                        change paln
                    </Link>


                    <form @submit.prevent="cancelSubscription" class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent">
    
    <button type="submit" class=" hover:cursor-pointer" >
        cancel subscription
    </button>
</form> -->



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

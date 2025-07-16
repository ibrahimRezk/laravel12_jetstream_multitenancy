<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import DeleteUserForm from "@/pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
// import SectionBorder from "@/components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/pages/Profile/Partials/UpdateProfileInformationForm.vue";

import { ref } from "vue";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Link, usePage } from "@inertiajs/vue3";
import AppearanceTabs from "@/components/AppearanceTabs.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";

const sidebarNavItems = [
    {
        title: "profile",
    },
    {
        title: "password",
    },
    {
        title: "two factor authentication",
    },
    {
        title: "browser sessions",
    },
    {
        title: "appearance",
        href: "/settings/appearance",
    },
    {
        title: "delete account",
    },
];

const page = usePage();

const currentItem = ref("profile");

// const currentPath = page.props.ziggy?.location
//     ? new URL(page.props.ziggy.location).pathname
//     : "";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12"
                >
                    <aside class="w-full max-w-xl lg:w-48">
                        <nav class="flex flex-col space-y-1 space-x-0">
                            <div
                                v-for="item in sidebarNavItems"
                                :key="item.title"
                            >
                                <Button
                                    variant="ghost"
                                    :class="[
                                        'w-full justify-start hover:cursor-pointer',
                                        {
                                            'bg-muted':
                                                currentItem === item.title,
                                        },
                                    ]"
                                    as-child
                                    @click="currentItem = item.title"
                                >
                                    <span>
                                        {{ item.title }}
                                    </span>
                                </Button>
                            </div>
                        </nav>
                    </aside>

                    <Separator class="my-6 md:hidden" />

                    <div class=" px-3 ">

                          <div
                        v-if="$page.props.jetstream.canUpdateProfileInformation"
                        v-show="currentItem == 'profile'"
                    >
                        <UpdateProfileInformationForm
                            :user="$page.props.auth.user"
                        />

                        <!-- <SectionBorder /> -->
                    </div>

                    <div
                        v-if="$page.props.jetstream.canUpdatePassword"
                        v-show="currentItem == 'password'"
                    >
                        <UpdatePasswordForm class="mt-10 sm:mt-0" />

                        <!-- <SectionBorder /> -->
                    </div>

                    <div>
                        <div
                            v-if="
                                $page.props.jetstream
                                    .canManageTwoFactorAuthentication
                            "
                            v-show="currentItem == 'two factor authentication'"
                        >
                            <TwoFactorAuthenticationForm
                                :requires-confirmation="
                                    confirmsTwoFactorAuthentication
                                "
                                class="mt-10 sm:mt-0"
                            />

                            <!-- <SectionBorder /> -->
                        </div>

                        <LogoutOtherBrowserSessionsForm
                            v-show="currentItem == 'browser sessions'"
                            :sessions="sessions"
                            class="mt-10 sm:mt-0"
                        />
                    </div>

                    <div class="space-y-6" v-show="currentItem == 'appearance'">
                        <HeadingSmall
                            title="Appearance settings"
                            description="Update your account's appearance settings"
                        />
                        <AppearanceTabs />
                    </div>

                    <div
                        v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                        v-show="currentItem == 'delete account'"
                    >
                        <!-- <SectionBorder /> -->

                        <DeleteUserForm class="mt-10 sm:mt-0" />
                    </div>
                    </div>
                  
                </div>
            </div>
        </div>
    </AppLayout>
</template>

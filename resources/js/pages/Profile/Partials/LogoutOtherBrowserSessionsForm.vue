<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionMessage from "@/components/ActionMessage.vue";
import ActionSection from "@/components/ActionSection.vue";
import DialogModal from "@/components/old/DialogModal.vue";
import PrimaryButton from "@/components/old/PrimaryButton.vue";
import SecondaryButton from "@/components/old/SecondaryButton.vue";
import TextInput from "@/components/old/TextInput.vue";


defineProps({
    sessions: Array,
});


import HeadingSmall from "@/components/HeadingSmall.vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger, 
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

const isDialogOpen = ref(false)

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = (e) => {
        e.preventDefault();

    form.delete(route("other-browser-sessions.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
      isDialogOpen.value = false


};
</script>

<template>
    <ActionSection>
        <template #title> Browser Sessions </template>

        <template #description>
            Manage and log out your active sessions on other browsers and
            devices.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                If necessary, you may log out of all of your other browser
                sessions across all of your devices. Some of your recent
                sessions are listed below; however, this list may not be
                exhaustive. If you feel your account has been compromised, you
                should also update your password.
            </div>

            <!-- Other Browser Sessions -->
            <div v-if="sessions.length > 0" class="mt-5 space-y-6">
                <div
                    v-for="(session, i) in sessions"
                    :key="i"
                    class="flex items-center"
                >
                    <div>
                        <svg
                            v-if="session.agent.is_desktop"
                            class="size-8 text-gray-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
                            />
                        </svg>

                        <svg
                            v-else
                            class="size-8 text-gray-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
                            />
                        </svg>
                    </div>

                    <div class="ms-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{
                                session.agent.platform
                                    ? session.agent.platform
                                    : "Unknown"
                            }}
                            -
                            {{
                                session.agent.browser
                                    ? session.agent.browser
                                    : "Unknown"
                            }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">
                                {{ session.ip_address }},

                                <span
                                    v-if="session.is_current_device"
                                    class="text-green-500 font-semibold"
                                    >This device</span
                                >
                                <span v-else
                                    >Last active {{ session.last_active }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center mt-5">
                <!-- <PrimaryButton @click="confirmLogout">
                    Log Out Other Browser Sessions
                </PrimaryButton> -->

                <ActionMessage :on="form.recentlySuccessful" class="ms-3">
                    Done.
                </ActionMessage>
            </div>

            <!-- Log Out Other Devices Confirmation Modal -->
           <Dialog  v-model:open="isDialogOpen">
                <DialogTrigger>
                    <Button variant="destructive"  > Log Out Other Browser Sessions</Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-6" @submit="logoutOtherBrowserSessions">
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                > Log Out Other Browser Sessions</DialogTitle
                            >
                            <DialogDescription> 
                                        Please enter your password to confirm you would like to log
                    out of your other browser sessions across all of your
                    devices.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >Password</Label
                            >
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                ref="passwordInput"
                                v-model="form.password"
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button  variant="secondary" @click="closeModal">
                                    Cancel
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="form.processing"
                            >
                               Log Out Other Browser Sessions
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>


        </template>
    </ActionSection>
</template>

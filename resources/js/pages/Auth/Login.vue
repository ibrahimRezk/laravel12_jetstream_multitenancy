<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/components/old/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/components/old/AuthenticationCardLogo.vue";
import Checkbox from "@/components/old/Checkbox.vue";
import InputError from "@/components/old/InputError.vue";
import InputLabel from "@/components/old/InputLabel.vue";
import PrimaryButton from "@/components/old/PrimaryButton.vue";
import TextInput from "@/components/old/TextInput.vue";

import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div
            v-if="status"
            class="mb-4 font-medium text-sm text-green-600 dark:text-green-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-2">
                <!-- <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" /> -->

                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="email"
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="mt-4 grid gap-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <!-- <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div> -->

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

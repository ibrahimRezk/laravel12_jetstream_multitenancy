<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/components/old/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/components/old/AuthenticationCardLogo.vue";
import Checkbox from "@/components/old/Checkbox.vue";
import InputError from "@/components/old/InputError.vue";
import InputLabel from "@/components/old/InputLabel.vue";
import PrimaryButton from "@/components/old/PrimaryButton.vue";
import TextInput from "@/components/old/TextInput.vue";
import { watch } from "vue";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
        subdomain: '',

    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};

// to prevent space here and there is another code in the input  take a look at it
watch(()=>form.subdomain  ,
()=> form.subdomain = form.subdomain.replace(/ +/g, '')  )


</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name"  > name </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

                        <div class="mt-4 grid gap-2">
                <Label for="subdomain"  > subdomain </Label>
                <Input
                    id="subdomain"
                    v-model="form.subdomain"
                    type="text"
                    class="mt-1 block w-full"

                    pattern="[A-Za-z0-9]+"
                    onkeydown="if(['Space'].includes(arguments[0].code)){return false;}"
                    
                    autofocus
                    autocomplete="subdomain"
                />
                <InputError class="mt-2" :message="form.errors.subdomain" />
            </div>

            <div class="mt-4 grid gap-2">
                <Label for="email"  > email </Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 grid gap-2">
                <Label for="password"  > password </Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 grid gap-2">
                <Label
                    for="password_confirmation"
                > confirm password </Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div
                v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                class="mt-4"
            >
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            
                        />

                        <div class="ms-2">
                            I agree to the
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                >Terms of Service</a
                            >
                            and
                            <a
                                target="_blank"
                                :href="route('policy.show')"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                >Privacy Policy</a
                            >
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

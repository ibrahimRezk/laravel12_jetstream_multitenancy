<script setup>
import { ref, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import ActionMessage from "@/components/ActionMessage.vue";
import ActionSection from "@/components/ActionSection.vue";
import DialogModal from "@/components/old/DialogModal.vue";
import PrimaryButton from "@/components/old/PrimaryButton.vue";
import SecondaryButton from "@/components/old/SecondaryButton.vue";
import TextInput from "@/components/old/TextInput.vue";

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

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
import { toRef } from "vue";

const props = defineProps({
    isDialogOpen: {
        type: Boolean,
        default: false,
    },

    item: {
        type: Object,
        default: {},
    },
    action: {
        type: String,
        default: "",
    },
    plans: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    subdomain: "",
    plan_id: "",
});

const fillForm = () => {
    form.name = props.item.name;
    form.email = props.item.email;
    // form.password = props.item?.user?.password
    // form.password_confirmation = props.item?.user?.password_confirmation
    form.subdomain = props.item?.domains[0].domain;
    form.plan_id = props.item?.subscription?.plan?.id;

    // Object.keys(form).forEach((key) => {
    //     item[key] !== undefined && key !== "name"
    //         ? (form[key] = item[key])
    //         : "";
    // });
};

const addNewSubscription = () => {
    form.post( route('admin.tenant.subscribe'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            // Optional: redirect to tenant dashboard
            // You can handle this in your backend controller instead
        },
        onError: (errors) => {
            if (errors.tenant_id) {
                modalError.value = errors.tenant_id;
            } else if (errors.message) {
                modalError.value = errors.message;
            } else {
                modalError.value = "An error occurred while subscriping";
            }
        },
    });
};

const editSubscription = () => {
    router.put(
        route(`admin.changeSubscription`, {
            tenantId: props.item.id,
            plan: form.plan_id,
        }),
        {
            ...form,
            id: props.item.id, // tenant id
            // data: itemToSave.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onBefore: () => {
                // isSaving.value = true;
            },
            onSuccess: () => {
                closeModal();

                // // form.reset()   /// try it
                // isSaving.value = false;
                // closeDialogModal();
            },
            onFinish: () => {
                // isSaving.value = false;
                // usePage().props.menus.forEach((menu) => {
                //     menu.isActive
                //         ? (menu.open = true)
                //         : (menu.open = false);
                // });
            },
        }
    );
};

const submit = () => {
    if (props.action == "edit") {
        editSubscription();
    } else {
        addNewSubscription();
    }
};

watch(
    () => props.isDialogOpen,
    () => (props.action == "edit" ? fillForm(props.item) : "")
);

watch(
    () => props.isDialogOpen,
    () => (props.isDialogOpen == false ? form.reset() : "")
);

const isDialogOpen = ref(false);

watch(
    () => props.isDialogOpen,
    () => (isDialogOpen.value = props.isDialogOpen)
);

watch(
    () => isDialogOpen.value,
    () => (isDialogOpen.value == false ? closeModal() : "")
);

const emit = defineEmits(["close"]);
const closeModal = () => {
    emit("close");
    form.reset();
    isDialogOpen.value = false;
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger>
            <Button class="hover:cursor-pointer"> add new tenant</Button>
        </DialogTrigger>
        <DialogContent>
            <form @submit.prevent="submit">
                <DialogHeader class="space-y-3">
                    <DialogTitle> add new tenant</DialogTitle>
                </DialogHeader>
                <DialogDescription> add new tenant details. </DialogDescription>

                <div class="grid gap-2 mt-4">
                    <Label for="name"> name </Label>
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
                    <Label for="email"> email </Label>
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
                    <Label for="password"> password </Label>
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
                    <Label for="password_confirmation">
                        confirm password
                    </Label>
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

                <div class="mt-4 grid gap-2">
                    <Label for="subdomain"> subdomain </Label>
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

                <div class="mt-2 mb-6 w-full">
                    <label class="flex items-center font-normal text-sm m-2">
                        plan
                    </label>
                    <Select v-model="form.plan_id">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select a plan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>plans</SelectLabel>
                                <SelectItem
                                    v-for="(item, index) in plans"
                                    :key="index"
                                    :value="item.id"
                                >
                                    {{ item.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="closeModal">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        class="hover:cursor-pointer"
                        type="submit"
                        :disabled="form.processing"
                    >
                        confirm
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

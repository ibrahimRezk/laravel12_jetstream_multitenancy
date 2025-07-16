<script setup>
import { ref, reactive, nextTick } from 'vue';
// import DialogModal from './DialogModal.vue';
import InputError from './InputError.vue';
import { Button } from '@/components/ui/button';

// import PrimaryButton from './PrimaryButton.vue';
// import SecondaryButton from './SecondaryButton.vue';
// import TextInput from './TextInput.vue';


import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";


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


const isDialogOpen = ref(false)

const emit = defineEmits(['confirmed']); 

defineProps({
    title: {
        type: String,
        default: 'Confirm Password',
    },
    content: {
        type: String,
        default: 'For your security, please confirm your password to continue.',
    },
    button: {
        type: String,
        default: 'Confirm',
    },
});

const confirmingPassword = ref(false);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

const passwordInput = ref(null);

const startConfirmingPassword = () => {
    axios.get(route('password.confirmation')).then(response => {
        if (response.data.confirmed) {
            emit('confirmed');
        } else {
            confirmingPassword.value = true;

            setTimeout(() => passwordInput.value.focus(), 250);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios.post(route('password.confirm'), {
        password: form.password,
    }).then(() => {
        form.processing = false;

        closeModal();
        nextTick().then(() => emit('confirmed'));

    }).catch(error => {
        form.processing = false;
        form.error = error.response.data.errors.password[0];
        passwordInput.value.focus();
    });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';

          isDialogOpen.value = false

};
</script>

<template>
    <span>
        <!-- <span @click="startConfirmingPassword">
            <slot />
        </span> -->

        <!-- <DialogModal :show="confirmingPassword" @close="closeModal">
            <template #title>
                {{ title }}
            </template>

            <template #content>
                {{ content }}

                <div class="mt-4">
                    <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        autocomplete="current-password"
                        @keyup.enter="confirmPassword"
                    />

                    <InputError :message="form.error" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="confirmPassword"
                >
                    {{ button }}
                </PrimaryButton>
            </template>
        </DialogModal> -->



                   <Dialog  v-model:open="isDialogOpen">
                <DialogTrigger>
                          <slot /> 
                </DialogTrigger>
                <DialogContent>
                    <div class="space-y-6" >
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                >  {{ title }} </DialogTitle
                            >
                            <DialogDescription> 
                                      {{ content }}
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

                              <!-- <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        autocomplete="current-password"
                        @keyup.enter="confirmPassword"
                    /> -->
                            <InputError :message="form.error" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button  variant="secondary" @click="closeModal">
                                    Cancel
                                </Button>
                            </DialogClose>

                            <Button
                                type="button"
                                variant="destructive"
                                @click="confirmPassword"
                                :disabled="form.processing"
                            >
                               {{ title }}
                            </Button>
                        </DialogFooter>
                    </div>
                </DialogContent>
            </Dialog>




    </span>
</template>

<script setup pages>
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps({
    title: String,
    description: String
});
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>

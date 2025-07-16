<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="md:grid ">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:col-span-2">
            <div class="px-4 py-5 sm:p-6 bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>

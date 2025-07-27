<script setup>
const props = defineProps({
    animate: {
        type: Boolean,
        default: () => true,
    },
});
</script>

<template>
    <transition name="page" mode="out-in">
        <div class="flex justify-center">
            <div
                class="w-full rounded-xl p-4 max-w-[1600px] bg-primary-foreground m-2 border"
            >
                <slot />
            </div>
        </div>
        <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> -->
        <!-- <div v-if="props.animate">
            <div
                class="w-full px-2 py-2 pb-10 rounded-xl shadow-md border border-gray-400/40 border-x-0 rtl:bg-gradient-to-r ltr:bg-gradient-to-l from-zinc-800/40 via-orange-200/40 to-zinc-800/40 dark:from-zinc-700/40 dark:to-zinc-800/40 dark:via-zinc-700/40 mx-auto max-w-[1800px]"
            >
                <slot />
            </div>
        </div> -->
    </transition>
</template>

<style scoped>
/* durations and timing functions.*/
.page-enter-active,
.page-leave-active {
    transition: all 0.8s;
}

.page-enter-from {
    transform: translateY(40px);
    opacity: 0;
}

.page-leave-to {
    opacity: 0;
    transform: translateY(-70px);
}
</style>

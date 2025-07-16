<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: { type: null, required: false },
});
</script>

<template>
  <main
    :class="
      cn(
        'relative flex min-h-svh flex-1 flex-col bg-background',
        'peer-data-[variant=inset]:min-h-[calc(100svh-(--spacing(4)))] md:peer-data-[variant=inset]:m-2 md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-2 md:peer-data-[variant=inset]:ml-0 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm z-10',
        props.class,
      )
    "
  >


    <div
  class="w-full h-full absolute top-0 left-0 bg-[url('/assets/img/noise.jpg')] bg-contain bg-center opacity-[.1]  dark:opacity-[.1]  -z-20 rounded-xl"
/>
<div
  class="w-full h-full absolute top-0 left-0 bg-[url('/assets/img/grid.svg')] bg-contain bg-center opacity-[.1] invert blur-[0.5px]  dark:blur-[0px] dark:invert-0  dark:opacity-[.1]  -z-20  rounded-xl"
/>


    <slot />
  </main>
</template>

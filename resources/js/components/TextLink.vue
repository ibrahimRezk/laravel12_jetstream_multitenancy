<script setup pages>
import { Method } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';

interface Props {
    href: string;
    tabindex?: number;
    method?: Method;
    as?: string;
}

const props = defineProps({
        href: {
        type: String,
        default: null,
    },
        tabindex: {
        type: Number,
        default: null,
    },
        method: {
        type: (),
        default: ()=> (),
    },
    as: {
        type: String,
        default: 16,
    }

});



// defineProps<Props>();
</script>

<template>
    <Link
        :href="href"
        :tabindex="tabindex"
        :method="method"
        :as="as"
        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
    >
        <slot />
    </Link>
</template>

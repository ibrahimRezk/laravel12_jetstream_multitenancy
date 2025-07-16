<script setup pages>
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

// interface Props {
//     name: string;
//     class?: string;
//     size?: number | string;
//     color?: string;
//     strokeWidth?: number | string;
// }

const props = defineProps({
        name: {
        type: String,
        default: null,
    },
        class: {
        type: String,
        default: null,
    },
        color: {
        type: String,
        default: null,
    },
    size: {
        type: Number,
        default: 16,
    },
    strokeWidth: {
        type: Number|String,
        default: 2,
    },

});


const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    const iconName = props.name.charAt(0).toUpperCase() + props.name.slice(1);
    return (icons as Record<string, any>)[iconName];
});
</script>

<template>
    <component
        :is="icon"
        :class="className"
        :size="size"
        :stroke-width="strokeWidth"
        :color="color"
    />
</template>

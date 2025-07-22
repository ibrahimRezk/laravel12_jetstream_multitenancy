<script setup>
import { ref, computed , watch} from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import SubscriptionCard from "@/components/SubscriptionCard.vue";

const props = defineProps({
    subscription: {
        type: Object,
        default: () => {},
    },

});



</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->


        <SubscriptionCard site-type="tenant" :subscription/>

    </div>
</template>

<style scoped>
.container {
    max-width: 1200px;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions */
.transition-transform {
    transition: transform 0.2s ease-in-out;
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Focus states */
select:focus,
button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Hover effects */
.hover\:scale-105:hover {
    transform: scale(1.05);
}
</style>
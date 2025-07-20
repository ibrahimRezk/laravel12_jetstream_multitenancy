<script setup>
import { ref, computed , watch} from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    subscription: {
        type: Object,
        default: () => {},
    },

});

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};

const formatFeature = (feature) => {
    return feature
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};


</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-300 mb-4">
                 Your subscription
            </h1>
   
        </div>

        <!-- subscriptions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <div
           
                class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform "
            >
          

                <div class="px-6 py-8">
                    <!--  Header -->
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ subscription.purchasePlan?.name }}
                        </h3>
                        <p class="text-gray-600 mb-4">
                            {{ subscription.purchasePlan?.description }}
                        </p>

                        <div class="mb-4">
                            <span class="text-4xl font-bold text-gray-900">
                                ${{ formatPrice(subscription.purchasePlan?.price) }}
                            </span>
                            <span class="text-gray-600"
                                > 
                                / {{ subscription.purchasePlan?.interval }}
                                </span
                            >
                        </div>

                        <!-- Trial Badge -->
                        <div
                            v-if="subscription.purchasePlan.trial_days > 0"
                            class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm inline-block mb-4"
                        >
                            {{ subscription.purchasePlan.trial_days }} days free trial
                        </div>
                    </div>

                    <!-- Features List -->
                    <ul class="space-y-3 mb-8">
                        <li
                            v-for="feature in subscription.purchasePlan.features"
                            :key="feature"
                            class="flex items-center"
                        >
                            <svg
                                class="w-5 h-5 text-green-500 mr-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                ></path>
                            </svg>
                            <span class="text-gray-700">{{
                                formatFeature(feature)
                            }}</span>
                        </li>
                    </ul>

                  
                </div>
            </div>
            <div
           
                class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform "
            >
          

                <div class="px-6 py-8">
                    <!--  Header -->
                    <div class="text-center mb-8">
                        
                        <p class="text-gray-600 mb-4">
                           status :  {{ subscription.status }}
                        </p>
                        <p class="text-gray-600 mb-4">
                           trial ends ad :   {{ subscription.trial_ends_at }}
                        </p>
                        <p class="text-gray-600 mb-4">
                           ends at :  {{ subscription.ends_at }}
                        </p>
                        <p class="text-gray-600 mb-4">
                           is active :  {{ subscription.is_active }}
                        </p>
                        <p class="text-gray-600 mb-4">
                           on trial :   {{ subscription.on_trial }}
                        </p>
                        <p class="text-gray-600 mb-4">
                          created at :   {{ subscription.created_at }}
                        </p>

                       

                  
                </div>
                </div>
            </div>
        </div>




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
<script setup>
import { ref, computed , watch} from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: () => null,
    },
    
    errors: {
        type: Object,
        default: () => {},
    },

});

const page = usePage();

// Reactive state
const showModal = ref(false);
const selectedPlan = ref(null);
const modalError = ref("");

// // Form for subscription
// const form = useForm({
//     tenant_id: "",
// });

const processing = ref(false)

// Computed properties
const toast = computed(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        return {
            show: true,
            type: "success",
            message: flash.success,
        };
    } else if (flash?.error) {
        return {
            show: true,
            type: "error",
            message: flash.error,
        };
    }
    return {
        show: false,
        type: "success",
        message: "",
    };
});

// watch(()=> usePage().props.flash.error, 
// ()=> usePage().props.flash.error != '' ? processing.value = false : '')
// watch(()=> usePage().props.flash.error, 
// ()=> usePage().props.flash.error != '' ? showModal.value = false : '')

// Methods
const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};

const formatFeature = (feature) => {
    return feature
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const openSubscriptionModal = (plan) => {
    selectedPlan.value = plan;
    // form.tenant_id = "";
    // form.clearErrors();
    modalError.value = "";
    showModal.value = true;
};

const closeModal = () => {
    if (processing.value == true ) return;

    showModal.value = false;
    selectedPlan.value = null;
    // form.reset();
    // form.clearErrors();
    modalError.value = "";
};



const confirmSubscription = () => {

props.type == null ? confirm() : change()

};
const confirm =()=> {
    if (!selectedPlan.value) {
        modalError.value = "No plan selected";
        return;
    }
    // if (!form.tenant_id) {
    //     modalError.value = "Please select a tenant";
    //     return;
    // }

    modalError.value = "";

    processing.value = true

    router.post(route(`tenant.subscribe` , selectedPlan.value.id),{}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
                processing.value = false

            closeModal();
            // Optional: redirect to tenant dashboard
            // You can handle this in your backend controller instead
        },
        onError: (errors) => {
            console.log(errors)
            // if (errors.tenant_id) {
            //     modalError.value = errors.tenant_id;
            // } else
             if (errors) {
                modalError.value = errors.error;
            } else {
                modalError.value = "An error occurred while subscribing";
            }
        },
    });
}

const change = ()=>{
     if (!selectedPlan.value) {
        modalError.value = "No plan selected";
        return;
    }

    modalError.value = "";

    processing.value = true

    router.put(route(`tenant.changeSubscription` , selectedPlan.value.id),{}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
                processing.value = false

            closeModal();
            // Optional: redirect to tenant dashboard
            // You can handle this in your backend controller instead
        },
        onError: (errors) => {
            console.log(errors)
            // if (errors.tenant_id) {
            //     modalError.value = errors.tenant_id;
            // } else
             if (errors) {
                modalError.value = errors.error;
            } else {
                modalError.value = "An error occurred while subscribing";
            }
        },
    });
}

// Keyboard event listeners
const handleEscape = (e) => {
    if (e.key === "Escape" && showModal.value) {
        closeModal();
    }
};

// Add event listener when component mounts
document.addEventListener("keydown", handleEscape);

// Cleanup when component unmounts
import { onUnmounted } from "vue";
onUnmounted(() => {
    document.removeEventListener("keydown", handleEscape);
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
         
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Choose Your Plan
            </h1>
            <p class="text-xl text-gray-600">
                Select the perfect plan for your business needs
            </p>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div
                v-for="(plan, index) in plans"
                :key="plan.id"
                class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform hover:scale-105"
                :class="{ 'ring-2 ring-blue-500 relative': index === 1 }"
            >
                <!-- Popular Badge -->
                <div
                    v-if="index === 1"
                    class="absolute top-0 left-1/2 transform -translate-x-1/2 mt-1"
                >
                    <span
                        class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium"
                    >
                        Most Popular
                    </span>
                </div>

                <div class="px-6 py-8">
                    <!-- Plan Header -->
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ plan.name }}
                        </h3>
                        <p class="text-gray-600 mb-4">{{ plan.description }}</p>

                        <div class="mb-4">
                            <span class="text-4xl font-bold text-gray-900">
                                ${{ formatPrice(plan.price) }}
                            </span>
                            <span class="text-gray-600"
                                >/ {{ plan.interval }}</span
                            >
                        </div>

                        <!-- Trial Badge -->
                        <div
                            v-if="plan.trial_days > 0"
                            class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm inline-block mb-4"
                        >
                            {{ plan.trial_days }} days free trial
                        </div>
                    </div>

                    <!-- Features List -->
                    <ul class="space-y-3 mb-8">
                        <li
                            v-for="feature in plan.features"
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

                    <!-- Subscribe Button -->
                    <button
                        @click="openSubscriptionModal(plan)"
                        :disabled="processing"
                        class="w-full font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="
                            index === 1
                                ? 'bg-blue-700 hover:bg-blue-800 text-white'
                                : 'bg-blue-600 hover:bg-blue-700 text-white'
                        "
                    >
                        <span
                            v-if="processing && selectedPlan?.id === plan.id"
                        >
                            <svg
                                class="inline w-4 h-4 mr-2 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>Get Started</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tenant Selection Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                @click="closeModal"
            >
                <div
                    class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
                    @click.stop
                >
                    <div class="mt-3 text-center">
                       
                        

                        <!-- Error Message -->
                        <div
                            v-if="modalError "
                            class="mt-2 text-red-600 text-sm"
                        >
                            {{ modalError }}
                        </div>

                        <div class="items-center px-4 py-3">
                            <button
                                @click="confirmSubscription"
                                :disabled=" processing"
                                class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="processing">
                                    <svg
                                        class="inline w-4 h-4 mr-2 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Processing...
                                </span>
                                <span v-else>Confirm Subscription</span>
                            </button>
                            <button
                                @click="closeModal"
                                :disabled="processing"
                                class="mt-2 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 disabled:opacity-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Success/Error Toast -->
        <Teleport to="body">
            <div
                v-if="toast.show"
                class="fixed top-4 right-4 z-50 max-w-sm w-full"
                :class="
                    toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
                "
            >
                <div class="rounded-lg shadow-lg p-4 text-white">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                v-if="toast.type === 'success'"
                                class="h-5 w-5"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                v-else
                                class="h-5 w-5"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">
                                {{ toast.message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
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
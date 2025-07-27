<script setup>

import { useSubscription } from "@/composables/useSubscription";
import { onMounted } from "vue";

const props = defineProps({
    subscription: {
        type: Object,
        default: null,
    },
    siteType: String
});

const emit = defineEmits(["view-plans", "change-plan", "cancel-subscription"]);

const {
    // init,
    fetchTenantSubscription,
    formatPrice,
    formatFeature,
    getDaysUntilExpiry,
    getDaysUntilTrialExpiry,
} = useSubscription();

// onMounted(()=>  init())
onMounted(()=>  fetchTenantSubscription(props.siteType , props.subscription.tenant_id))

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const getStatusBadgeClass = () => {
    if (!props.subscription) return "";

    const status = props.subscription.status;

    switch (status) {
        case "active":
            return "bg-green-100 text-green-800";
        case "cancelled":
            return "bg-red-100 text-red-800";
        case "expired":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div v-if="!subscription" class="text-center py-8">
            <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <h3 class="mt-4 text-sm font-medium text-gray-900">
                No Active Subscription
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Choose a plan to get started
            </p>
            <div class="mt-6">
                <button
                    @click="$emit('view-plans')"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                    View Plans
                </button>
            </div>
        </div>

        <div v-else>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Current Plan</h3>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusBadgeClass()"
                >
                    {{ formatStatus(subscription.status) }}
                </span>
            </div>

            <div class="mb-4">
                <h4 class="text-xl font-bold text-gray-900">
                    {{ subscription.plan.name }}
                </h4>
                <p class="text-gray-600">{{ subscription.plan.description }}</p>
            </div>

            <div class="mb-4">
                <div class="flex items-baseline">
                    <span class="text-2xl font-bold text-gray-900">
                        ${{ formatPrice(subscription.plan.price) }}
                    </span>
                    <span class="text-gray-600 ml-1"
                        >/ {{ subscription.plan.interval }}</span
                    >
                </div>
            </div>

            <!-- Trial Information -->
            <div
                v-if="subscription.on_trial"
                class="mb-4 p-3 bg-yellow-50 rounded-lg"
            >
                <div class="flex">
                    <svg
                        class="h-5 w-5 text-yellow-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800">
                            <strong>Trial Period:</strong>
                            {{ getDaysUntilTrialExpiry() }} days remaining
                        </p>
                    </div>
                </div>
            </div>

            <!-- Expiry Information -->
            <div
                v-else-if="subscription.is_active"
                class="mb-4 p-3 bg-gray-50 rounded-lg"
            >
                <p class="text-sm text-gray-700">
                    <strong>Renews on:</strong>
                    {{ formatDate(subscription.ends_at) }}
                </p>
                <p
                    v-if="getDaysUntilExpiry() <= 7"
                    class="text-sm text-orange-600 mt-1"
                >
                    <strong>Expires in {{ getDaysUntilExpiry() }} days</strong>
                </p>
            </div>

            <!-- Features -->
            <div class="mb-6">
                <h5 class="text-sm font-medium text-gray-900 mb-2">Features</h5>
                <ul class="space-y-1">
                    <li
                        v-for="feature in subscription.plan.features"
                        :key="feature"
                        class="flex items-center text-sm text-gray-600"
                    >
                        <svg
                            class="h-4 w-4 text-green-500 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ formatFeature(feature) }}
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button
                    @click="$emit('change-plan')"
                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
                >
                    Change Plan
                </button>
                <button
                    @click="$emit('cancel-subscription')"
                    class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

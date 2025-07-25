import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useSubscription() {
  // State
  const plans = ref([])
  const tenants = ref([])
  const currentSubscription = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const subscribing = ref(false)

  // Computed
  const hasActiveSubscription = computed(() => {
    return currentSubscription.value && currentSubscription.value.is_active
  })

  const isOnTrial = computed(() => {
    return currentSubscription.value && currentSubscription.value.on_trial
  })

  const subscriptionFeatures = computed(() => {
    return currentSubscription.value?.purchasePlan?.features || []
  })

  // Inertia Methods
  const fetchPlans = async () => {
    try {
      loading.value = true
      error.value = null
      
      router.get('/purchase-plan', {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['plans'],
        onSuccess: (page) => {
          plans.value = page.props.plans || []
        },
        onError: (errors) => {
          error.value = 'Failed to fetch plans'
          console.error('Error fetching plans:', errors)
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (err) {
      error.value = err.message || 'Failed to fetch plans'
      console.error('Error fetching plans:', err)
      loading.value = false
    }
  }

  const fetchTenants = async () => {
    try {
      router.get('/tenants', {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['tenants'],
        onSuccess: (page) => {
          tenants.value = page.props.tenants || []
        },
        onError: (errors) => {
          console.error('Error fetching tenants:', errors)
        }
      })
    } catch (err) {
      console.error('Error fetching tenants:', err)
    }
  }

  const fetchTenantSubscription = async (sitetype , tenantId) => {
    try {
      // router.get(`/tenant/${tenantId}/subscription`, {}, {
      router.get(route(`${sitetype}.getTenantSubscription` , {tenantId : tenantId}), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['subscription'],
        onSuccess: (page) => {
          currentSubscription.value = page.props.subscription
          console.log(currentSubscription.value)
        },
        onError: (errors) => {
          console.error('Error fetching tenant subscription:', errors)
        }
      })
    } catch (err) {
      console.error('Error fetching tenant subscription:', err)
    }
  }

  const subscribeToPlan = async (planId, tenantId) => {
    return new Promise((resolve, reject) => {
      try {
        subscribing.value = true
        error.value = null
        
        router.post(`/subscribe/${planId}`, {
          tenant_id: tenantId
        }, {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            // Update current subscription after successful subscription
            fetchTenantSubscription(tenantId)
            resolve(page.props)
          },
          onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Failed to subscribe'
            error.value = errorMessage
            reject(new Error(errorMessage))
          },
          onFinish: () => {
            subscribing.value = false
          }
        })
      } catch (err) {
        error.value = err.message || 'Failed to subscribe'
        subscribing.value = false
        reject(err)
      }
    })
  }

  const cancelSubscription = async (tenantId) => {
    return new Promise((resolve, reject) => {
      try {
        loading.value = true
        error.value = null
        
        router.delete(`/tenant/${tenantId}/subscription`, {}, {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            // Update current subscription after successful cancellation
            fetchTenantSubscription(tenantId)
            resolve(page.props)
          },
          onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Failed to cancel subscription'
            error.value = errorMessage
            reject(new Error(errorMessage))
          },
          onFinish: () => {
            loading.value = false
          }
        })
      } catch (err) {
        error.value = err.message || 'Failed to cancel subscription'
        loading.value = false
        reject(err)
      }
    })
  }

  const changeSubscription = async (tenantId, planId) => {
    return new Promise((resolve, reject) => {
      try {
        loading.value = true
        error.value = null
        
        router.put(`/tenant/${tenantId}/subscription/${planId}`, {}, {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            // Update current subscription after successful change
            fetchTenantSubscription(tenantId)
            resolve(page.props)
          },
          onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Failed to change subscription'
            error.value = errorMessage
            reject(new Error(errorMessage))
          },
          onFinish: () => {
            loading.value = false
          }
        })
      } catch (err) {
        error.value = err.message || 'Failed to change subscription'
        loading.value = false
        reject(err)
      }
    })
  }

  // Utility methods (unchanged)
  const formatPrice = (price) => {
    return parseFloat(price).toFixed(2)
  }

  const formatFeature = (feature) => {
    return feature.split('_').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
  }

  const canAccessFeature = (feature) => {
    return subscriptionFeatures.value.includes(feature)
  }

  const getDaysUntilExpiry = () => {
    if (!currentSubscription.value?.ends_at) return null
    
    const now = new Date()
    const expiryDate = new Date(currentSubscription.value.ends_at)
    const diffTime = expiryDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    return diffDays > 0 ? diffDays : 0
  }

  const getDaysUntilTrialExpiry = () => {
    if (!currentSubscription.value?.trial_ends_at) return null
    
    const now = new Date()
    const trialExpiry = new Date(currentSubscription.value.trial_ends_at)
    console.log(trialExpiry)
    const diffTime = trialExpiry - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    return diffDays > 0 ? diffDays : 0
  }

  const getSubscriptionStatus = () => {
    if (!currentSubscription.value) return 'none'
    
    const subscription = currentSubscription.value
    
    if (subscription.on_trial) {
      const trialDays = getDaysUntilTrialExpiry()
      if (trialDays > 0) return 'trial'
      return 'trial_expired'
    }
    
    if (subscription.is_active) {
      const days = getDaysUntilExpiry()
      if (days > 7) return 'active'
      if (days > 0) return 'expiring_soon'
      return 'expired'
    }
    
    return subscription.status
  }

  // Initialize - Using Inertia visit for initial page load
  const init = () => {

    // With Inertia, initial data is typically loaded via the controller
    // You can call these if you need to refresh data programmatically
    fetchPlans()
    fetchTenants()
  }

  return {
    // State
    plans,
    tenants,
    currentSubscription,
    loading,
    error,
    subscribing,
    
    // Computed
    hasActiveSubscription,
    isOnTrial,
    subscriptionFeatures,
    
    // Methods
    fetchPlans,
    fetchTenants,
    fetchTenantSubscription,
    subscribeToPlan,
    cancelSubscription,
    changeSubscription,
    
    // Utilities
    formatPrice,
    formatFeature,
    canAccessFeature,
    getDaysUntilExpiry,
    getDaysUntilTrialExpiry,
    getSubscriptionStatus,
    
    // Initialize
    init
  }
}
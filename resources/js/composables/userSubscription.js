import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useSubscription() {
  // State
  const plans = ref([])
  const tenants = ref([])
  const currentSubscription = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const subscribing = ref(false)

  // Computed
  const csrfToken = computed(() => {
    const token = document.querySelector('meta[name="csrf-token"]')
    return token ? token.getAttribute('content') : ''
  })

  const hasActiveSubscription = computed(() => {
    return currentSubscription.value && currentSubscription.value.is_active
  })

  const isOnTrial = computed(() => {
    return currentSubscription.value && currentSubscription.value.on_trial
  })

  const subscriptionFeatures = computed(() => {
    return currentSubscription.value?.plan?.features || []
  })

  // API Methods
  const fetchPlans = async () => {
    try {
      loading.value = true
      error.value = null
      
      const response = await fetch('/api/purchase-plan', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        }
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      plans.value = data.plans || []
    } catch (err) {
      error.value = err.message || 'Failed to fetch plans'
      console.error('Error fetching plans:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchTenants = async () => {
    try {
      const response = await fetch('/api/tenants', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        }
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      tenants.value = data.tenants || []
    } catch (err) {
      console.error('Error fetching tenants:', err)
    }
  }

  const fetchTenantSubscription = async (tenantId) => {
    try {
      const response = await fetch(`/api/tenant/${tenantId}/subscription`, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        }
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      currentSubscription.value = data.subscription
    } catch (err) {
      console.error('Error fetching tenant subscription:', err)
    }
  }

  const subscribeToPlan = async (planId, tenantId) => {
    try {
      subscribing.value = true
      error.value = null
      
      const response = await fetch(`/api/subscribe/${planId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        },
        body: JSON.stringify({
          tenant_id: tenantId
        })
      })
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to subscribe')
      }
      
      // Update current subscription
      await fetchTenantSubscription(tenantId)
      
      return data
    } catch (err) {
      error.value = err.message || 'Failed to subscribe'
      throw err
    } finally {
      subscribing.value = false
    }
  }

  const cancelSubscription = async (tenantId) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await fetch(`/api/tenant/${tenantId}/subscription`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        }
      })
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to cancel subscription')
      }
      
      // Update current subscription
      await fetchTenantSubscription(tenantId)
      
      return data
    } catch (err) {
      error.value = err.message || 'Failed to cancel subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  const changeSubscription = async (tenantId, planId) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await fetch(`/api/tenant/${tenantId}/subscription/${planId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken.value
        }
      })
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to change subscription')
      }
      
      // Update current subscription
      await fetchTenantSubscription(tenantId)
      
      return data
    } catch (err) {
      error.value = err.message || 'Failed to change subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Utility methods
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

  // Initialize
  const init = async () => {
    await Promise.all([
      fetchPlans(),
      fetchTenants()
    ])
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

// composables/useToast.js


const toasts = ref([])

export function useToast() {
  const showToast = (message, type = 'success', duration = 5000) => {
    const id = Date.now()
    const toast = {
      id,
      message,
      type,
      show: true
    }
    
    toasts.value.push(toast)
    
    setTimeout(() => {
      hideToast(id)
    }, duration)
    
    return id
  }

  const hideToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const clearAllToasts = () => {
    toasts.value = []
  }

  return {
    toasts,
    showToast,
    hideToast,
    clearAllToasts
  }
}


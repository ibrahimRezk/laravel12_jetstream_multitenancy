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

  // Helper method to show flash messages from Inertia
  const showFlashMessage = (page) => {
    if (page.props.flash?.success) {
      showToast(page.props.flash.success, 'success')
    }
    if (page.props.flash?.error) {
      showToast(page.props.flash.error, 'error')
    }
    if (page.props.flash?.warning) {
      showToast(page.props.flash.warning, 'warning')
    }
    if (page.props.flash?.info) {
      showToast(page.props.flash.info, 'info')
    }
  }

  return {
    toasts,
    showToast,
    hideToast,
    clearAllToasts,
    showFlashMessage
  }
}
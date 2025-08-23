import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import type { SharedData } from '@/types'

/**
 * Composable for handling global notifications
 * Automatically displays success and error messages from Inertia responses
 */
export function useNotifications() {
  const page = usePage<SharedData>()
  
  // Watch for success messages
  watch(
    () => page.props.success,
    (newSuccess) => {
      if (newSuccess) {
        toast.success(newSuccess, {
          duration: 3000,
        })
      }
    },
    { immediate: true }
  )
  
  // Watch for error messages
  watch(
    () => page.props.errors,
    (newErrors) => {
      if (newErrors && Object.keys(newErrors).length > 0) {
        // Show first error message
        const firstError = Object.values(newErrors)[0]
        if (typeof firstError === 'string') {
          toast.error(firstError, {
            duration: 5000,
          })
        }
      }
    },
    { immediate: true }
  )
  
  // Manual notification methods
  const showSuccess = (message: string, duration = 3000) => {
    toast.success(message, { duration })
  }
  
  const showError = (message: string, duration = 5000) => {
    toast.error(message, { duration })
  }
  
  const showInfo = (message: string, duration = 3000) => {
    toast.info(message, { duration })
  }
  
  const showWarning = (message: string, duration = 4000) => {
    toast.warning(message, { duration })
  }
  
  // Специфичные методы для приложения
  const showAdDeleted = () => {
    toast.success('Объявление удалено', { duration: 3000 })
  }
  
  const showAdCreated = () => {
    toast.success('Объявление создано', { duration: 3000 })
  }
  
  const showAdUpdated = () => {
    toast.success('Объявление обновлено', { duration: 3000 })
  }
  
  return {
    showSuccess,
    showError,
    showInfo,
    showWarning,
    showAdDeleted,
    showAdCreated,
    showAdUpdated,
  }
}

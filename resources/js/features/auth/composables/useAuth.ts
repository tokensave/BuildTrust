import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import type { SharedData, User } from '@/types'

/**
 * Composable for authentication functionality
 */
export function useAuth() {
  const page = usePage<SharedData>()
  
  // Computed properties
  const user = computed(() => page.props.auth.user as User | null)
  const isAuthenticated = computed(() => !!user.value)
  const isGuest = computed(() => !user.value)
  
  // User role checks
  const isDirector = computed(() => user.value?.role === 'director')
  const isEmployee = computed(() => user.value?.role === 'employee')
  
  // Logout functionality
  const logout = () => {
    router.post(route('logout'))
  }
  
  return {
    // State
    user,
    isAuthenticated,
    isGuest,
    
    // Role checks
    isDirector,
    isEmployee,
    
    // Actions
    logout,
  }
}

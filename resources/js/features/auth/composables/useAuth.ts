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

  const avatarUrl = computed(() => {
        if (user.value?.avatar_url) {
            // Используем avatar_url из данных пользователя, если он есть
            return user.value.avatar_url.trim();
        }

        // Если avatar_url нет или он пустой, генерируем URL с помощью ui-avatars.com
        const name = user.value?.username || 'U';
        // Убедитесь, что цвета указаны с решёткой (#), иначе ui-avatars может выдавать ошибку
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0369a1&color=ffffff`;
    });
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

    avatarUrl,
    // Actions
    logout,
  }
}

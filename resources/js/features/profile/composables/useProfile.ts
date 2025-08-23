import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import type { SharedData, User } from '@/types'

/**
 * Composable for user profile functionality
 */
export function useProfile() {
  const page = usePage<SharedData>()
  const user = computed(() => page.props.auth.user as User)
  const company = computed(() => page.props.company)
  
  // Avatar preview
  const avatarPreview = ref<string | null>(
    (user.value.media?.[0]?.original_url as string) || null
  )
  
  // Profile form
  const profileForm = useForm({
    username: user.value.username ?? '',
    email: user.value.email,
    company_name: company.value?.name ?? '',
    inn: company.value?.inn ?? '',
    phone: company.value?.phone ?? '',
    city: company.value?.city ?? '',
    address: company.value?.address ?? '',
    website: company.value?.website ?? '',
    verified: company.value?.verified ?? false,
    avatar: null as File | null,
  })
  
  // Handle avatar change
  const handleAvatarChange = (event: Event) => {
    const file = (event.target as HTMLInputElement)?.files?.[0] || null
    profileForm.avatar = file
    
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        avatarPreview.value = e.target?.result as string
      }
      reader.readAsDataURL(file)
    }
  }
  
  // Submit profile changes
  const updateProfile = () => {
    profileForm.post(route('profile.update'), {
      preserveScroll: true,
      forceFormData: true,
    })
  }
  
  return {
    // State
    user,
    company,
    avatarPreview,
    
    // Forms
    profileForm,
    
    // Actions
    handleAvatarChange,
    updateProfile,
  }
}

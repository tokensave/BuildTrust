import { onMounted } from 'vue'
import { useEnums } from './data/useEnums'

/**
 * Композабл для инициализации приложения
 * Загружает все необходимые данные при старте
 */
export function useAppInit() {
  const { loadEnums, loading: enumsLoading, error: enumsError } = useEnums()
  
  // Инициализация при монтировании приложения
  const initializeApp = async () => {
    try {
      await loadEnums()
    } catch (error) {
      console.error('Failed to initialize app:', error)
    }
  }
  
  // Автоматическая инициализация при монтировании
  onMounted(initializeApp)
  
  return {
    initializeApp,
    enumsLoading,
    enumsError,
  }
}

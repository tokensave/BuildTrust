/**
 * 📋 useEnums - Управление справочными данными (enum'ы)
 * 
 * Централизованное получение и кеширование всех enum'ов с бэкенда.
 * Заменяет хардкод на фронтенде единым источником истины.
 * 
 * @example
 * ```typescript
 * const { enums, loading, getStatusLabel, getStatusColor } = useEnums()
 * 
 * // Получение данных
 * await loadEnums()
 * 
 * // Использование
 * const statusLabel = getStatusLabel('published', 'ad')
 * const statusColor = getStatusColor('pending', 'deal')
 * ```
 */

import { ref, computed } from 'vue'
import type { EnumOption } from '@/shared/types/api'

interface AppEnums {
    adStatuses: EnumOption[]
    adTypes: EnumOption[]
    dealStatuses: EnumOption[]
    categories_structure: Record<string, {
        label: string
        subcategories: Record<string, EnumOption>
    }>
}

const enums = ref<AppEnums>({
    adStatuses: [],
    adTypes: [],
    dealStatuses: [],
    categories_structure: {}
})

const loading = ref(false)
const error = ref<string | null>(null)

export function useEnums() {
    // Загрузка всех enum'ов с API
    const loadEnums = async () => {
        if (enums.value.adStatuses.length > 0) return // Уже загружены

        loading.value = true
        error.value = null

        try {
            const response = await fetch('/api/filters/all-enums')
            if (!response.ok) throw new Error('Ошибка загрузки справочников')
            
            const data = await response.json()
            enums.value = {
                adStatuses: data.adStatuses || [],
                adTypes: Object.values(data.adTypes || {}),
                dealStatuses: data.dealStatuses || [],
                categories_structure: data.categories_structure || {}
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Неизвестная ошибка'
            console.error('Ошибка загрузки enum\'ов:', err)
        } finally {
            loading.value = false
        }
    }

    // Получение лейбла статуса
    const getStatusLabel = (status: string, type: 'ad' | 'deal' = 'ad'): string => {
        const statusList = type === 'ad' ? enums.value.adStatuses : enums.value.dealStatuses
        return statusList.find(s => s.value === status)?.label || status
    }

    // Получение цвета статуса
    const getStatusColor = (status: string, type: 'ad' | 'deal' = 'ad'): string => {
        const statusList = type === 'ad' ? enums.value.adStatuses : enums.value.dealStatuses
        return statusList.find(s => s.value === status)?.color || 'gray'
    }

    // Получение типов объявлений
    const getAdTypes = computed(() => enums.value.adTypes)

    // Получение категорий по типу объявления
    const getCategoriesByType = (type: string) => {
        // Логика фильтрации категорий по типу
        const allCategories = enums.value.categories_structure
        
        if (type === 'goods') {
            return Object.fromEntries(
                Object.entries(allCategories).filter(([key]) => 
                    ['materials', 'tools', 'equipment'].includes(key)
                )
            )
        } else if (type === 'services') {
            return Object.fromEntries(
                Object.entries(allCategories).filter(([key]) => 
                    ['construction', 'repair', 'design'].includes(key)
                )
            )
        }
        
        return allCategories
    }

    // Получение подкатегорий по категории
    const getSubcategoriesByCategory = (category: string) => {
        return enums.value.categories_structure[category]?.subcategories || {}
    }

    // Проверка загрузки
    const isLoaded = computed(() => enums.value.adStatuses.length > 0)

    return {
        // Состояние
        enums,
        loading,
        error,
        isLoaded,

        // Методы
        loadEnums,
        getStatusLabel,
        getStatusColor,
        getCategoriesByType,
        getSubcategoriesByCategory,

        // Вычисляемые свойства
        getAdTypes,
    }
}

/**
 * 📢 useAds - Основной композабл для работы с объявлениями
 * 
 * Содержит всю логику для загрузки, управления и отображения объявлений.
 * Интегрируется с API и обеспечивает реактивность данных.
 * 
 * @example
 * ```typescript
 * const { ads, loading, loadAds, refreshAds } = useAds()
 * 
 * // Загрузка всех объявлений
 * await loadAds()
 * 
 * // Загрузка объявлений пользователя
 * await loadUserAds(userId)
 * 
 * // Обновление списка
 * await refreshAds()
 * ```
 */

import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import type { Ad, AdFilters } from '@/features/ads/types/ad'
import type { PaginatedResponse } from '@/shared/types/api'
import { useNotifications } from '@/shared/composables/useNotifications'

const ads = ref<Ad[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCount = ref(0)

export function useAds() {
    const { showError } = useNotifications()

    // Загрузка всех опубликованных объявлений
    const loadAds = async (filters: AdFilters = {}, page = 1) => {
        loading.value = true
        error.value = null

        try {
            // Используем Inertia для навигации с фильтрами
            router.get('/dashboard', { 
                ...filters, 
                page 
            }, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page: any) => {
                    const adsData = page.props.ads
                    ads.value = adsData.data
                    currentPage.value = adsData.current_page
                    totalPages.value = adsData.last_page
                    totalCount.value = adsData.total
                },
                onError: (errors) => {
                    error.value = 'Ошибка загрузки объявлений'
                    showError('Не удалось загрузить объявления')
                },
                onFinish: () => {
                    loading.value = false
                }
            })
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Неизвестная ошибка'
            showError('Ошибка при загрузке объявлений')
            loading.value = false
        }
    }

    // Загрузка объявлений пользователя
    const loadUserAds = async (userId: number) => {
        loading.value = true
        error.value = null

        try {
            router.get(`/user/${userId}/ads`, {}, {
                onSuccess: (page: any) => {
                    ads.value = page.props.ads
                },
                onError: () => {
                    error.value = 'Ошибка загрузки объявлений пользователя'
                    showError('Не удалось загрузить ваши объявления')
                },
                onFinish: () => {
                    loading.value = false
                }
            })
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Неизвестная ошибка'
            showError('Ошибка при загрузке объявлений')
            loading.value = false
        }
    }

    // Получение конкретного объявления
    const getAdById = async (adId: number): Promise<Ad | null> => {
        try {
            const response = await fetch(`/api/ads/${adId}`)
            if (!response.ok) throw new Error('Объявление не найдено')
            
            const data = await response.json()
            return data.data
        } catch (err) {
            showError('Объявление не найдено')
            return null
        }
    }

    // Обновление списка (без изменения фильтров)
    const refreshAds = async () => {
        // Просто перезагружаем текущую страницу
        router.reload({ 
            preserveScroll: true,
            onStart: () => { loading.value = true },
            onFinish: () => { loading.value = false }
        })
    }

    // Вычисляемые свойства
    const hasAds = computed(() => ads.value.length > 0)
    const isEmpty = computed(() => !loading.value && ads.value.length === 0)
    const hasError = computed(() => error.value !== null)

    // Группировка объявлений по статусу (для личного кабинета)
    const adsByStatus = computed(() => {
        return ads.value.reduce((groups, ad) => {
            const status = ad.status
            if (!groups[status]) {
                groups[status] = []
            }
            groups[status].push(ad)
            return groups
        }, {} as Record<string, Ad[]>)
    })

    // Счетчики по статусам
    const statusCounts = computed(() => {
        return {
            draft: ads.value.filter(ad => ad.status === 'draft').length,
            published: ads.value.filter(ad => ad.status === 'published').length,
            archived: ads.value.filter(ad => ad.status === 'archived').length,
        }
    })

    return {
        // Реактивные данные
        ads,
        loading,
        error,
        currentPage,
        totalPages,
        totalCount,

        // Методы
        loadAds,
        loadUserAds,
        getAdById,
        refreshAds,

        // Вычисляемые свойства
        hasAds,
        isEmpty,
        hasError,
        adsByStatus,
        statusCounts,
    }
}

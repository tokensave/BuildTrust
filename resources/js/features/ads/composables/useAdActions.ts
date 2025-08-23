/**
 * ⚡ useAdActions - Композабл для действий с объявлениями
 * 
 * Централизует все действия над объявлениями: удаление, изменение статуса,
 * дублирование и другие операции.
 * 
 * @example
 * ```typescript
 * const { deleteAd, publishAd, archiveAd, duplicateAd } = useAdActions()
 * 
 * // Удалить объявление
 * await deleteAd(adId, userId)
 * 
 * // Опубликовать
 * await publishAd(adId, userId)
 * ```
 */

import { router } from '@inertiajs/vue3'
import type { Ad } from '@/features/ads/types/ad'
import { useNotifications } from '@/shared/composables/core/useNotifications'
import { useConfirmDialog } from '@/shared/composables/ui/useConfirmDialog'

export function useAdActions() {
    const { 
        showAdDeleted, 
        showAdPublished, 
        showAdArchived, 
        showError,
        showSuccess 
    } = useNotifications()
    const { confirm } = useConfirmDialog()

    /**
     * Удаление объявления с подтверждением
     */
    const deleteAd = async (adId: number, userId: number): Promise<boolean> => {
        const isConfirmed = await confirm({
            title: 'Удаление объявления',
            message: 'Вы уверены, что хотите удалить это объявление? Это действие нельзя отменить.',
            confirmText: 'Удалить',
            cancelText: 'Отмена',
            type: 'danger'
        })

        if (!isConfirmed) return false

        return new Promise((resolve) => {
            router.delete(route('user.ads.destroy', { user: userId, ad: adId }), {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {
                    showAdDeleted()
                    resolve(true)
                },
                onError: (errors) => {
                    console.error('Ошибка удаления объявления:', errors)
                    showError('Невозможно удалить объявление при наличии незакрытых сделок.')
                    resolve(false)
                }
            })
        })
    }

    /**
     * Публикация объявления
     */
    const publishAd = async (adId: number, userId: number): Promise<boolean> => {
        return updateAdStatus(adId, userId, 'published', 'Опубликовать объявление?', showAdPublished)
    }

    /**
     * Архивирование объявления  
     */
    const archiveAd = async (adId: number, userId: number): Promise<boolean> => {
        return updateAdStatus(adId, userId, 'archived', 'Переместить объявление в архив?', showAdArchived)
    }

    /**
     * Возврат в черновики
     */
    const moveToDraft = async (adId: number, userId: number): Promise<boolean> => {
        return updateAdStatus(adId, userId, 'draft', 'Переместить объявление в черновики?', () => {
            showSuccess('Объявление перемещено в черновики')
        })
    }

    /**
     * Универсальная функция изменения статуса
     */
    const updateAdStatus = async (
        adId: number, 
        userId: number, 
        status: string, 
        confirmMessage: string,
        onSuccessCallback: () => void
    ): Promise<boolean> => {
        const isConfirmed = await confirm({
            title: 'Изменение статуса',
            message: confirmMessage,
            confirmText: 'Да',
            cancelText: 'Отмена'
        })

        if (!isConfirmed) return false

        return new Promise((resolve) => {
            router.put(route('user.ads.update', { user: userId, ad: adId }), {
                status: status
            }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    onSuccessCallback()
                    resolve(true)
                },
                onError: (errors) => {
                    console.error('Ошибка изменения статуса:', errors)
                    showError('Не удалось изменить статус объявления')
                    resolve(false)
                }
            })
        })
    }

    /**
     * Дублирование объявления
     */
    const duplicateAd = async (ad: Ad, userId: number): Promise<boolean> => {
        const isConfirmed = await confirm({
            title: 'Дублировать объявление',
            message: `Создать копию объявления "${ad.title}"?`,
            confirmText: 'Создать копию',
            cancelText: 'Отмена'
        })

        if (!isConfirmed) return false

        // Подготавливаем данные для дублирования (убираем id и меняем заголовок)
        const duplicateData = {
            title: `${ad.title} (копия)`,
            description: ad.description,
            type: ad.type,
            category: ad.category,
            subcategory: ad.subcategory,
            price: ad.price,
            location: ad.location,
            features: ad.features,
            is_urgent: false, // Сбрасываем срочность
            status: 'draft' // Всегда создаем как черновик
        }

        return new Promise((resolve) => {
            router.post(route('user.ads.store', userId), duplicateData, {
                onSuccess: () => {
                    showSuccess('Копия объявления создана')
                    resolve(true)
                },
                onError: (errors) => {
                    console.error('Ошибка дублирования объявления:', errors)
                    showError('Не удалось создать копию объявления')
                    resolve(false)
                }
            })
        })
    }

    /**
     * Массовое удаление объявлений
     */
    const deleteMultipleAds = async (adIds: number[], userId: number): Promise<boolean> => {
        const isConfirmed = await confirm({
            title: 'Массовое удаление',
            message: `Вы уверены, что хотите удалить ${adIds.length} объявлений? Это действие нельзя отменить.`,
            confirmText: 'Удалить все',
            cancelText: 'Отмена',
            type: 'danger'
        })

        if (!isConfirmed) return false

        // В реальности здесь должен быть API endpoint для массового удаления
        // Пока делаем через Promise.all
        try {
            const results = await Promise.all(
                adIds.map(adId => deleteAd(adId, userId))
            )
            
            const successCount = results.filter(Boolean).length
            if (successCount > 0) {
                showSuccess(`Удалено объявлений: ${successCount}`)
            }
            
            return successCount === adIds.length
        } catch (error) {
            console.error('Ошибка массового удаления:', error)
            showError('Произошла ошибка при удалении объявлений')
            return false
        }
    }

    /**
     * Массовое изменение статуса
     */
    const updateMultipleAdsStatus = async (
        adIds: number[], 
        userId: number, 
        status: string
    ): Promise<boolean> => {
        const statusLabels = {
            'draft': 'черновики',
            'published': 'опубликованные',
            'archived': 'архив'
        }

        const label = statusLabels[status as keyof typeof statusLabels] || status

        const isConfirmed = await confirm({
            title: 'Массовое изменение статуса',
            message: `Переместить ${adIds.length} объявлений в "${label}"?`,
            confirmText: 'Да',
            cancelText: 'Отмена'
        })

        if (!isConfirmed) return false

        try {
            const results = await Promise.all(
                adIds.map(adId => updateAdStatus(adId, userId, status, '', () => {}))
            )
            
            const successCount = results.filter(Boolean).length
            if (successCount > 0) {
                showSuccess(`Статус изменен у ${successCount} объявлений`)
            }
            
            return successCount === adIds.length
        } catch (error) {
            console.error('Ошибка массового изменения статуса:', error)
            showError('Произошла ошибка при изменении статуса')
            return false
        }
    }

    return {
        // Основные действия
        deleteAd,
        publishAd,
        archiveAd,
        moveToDraft,
        duplicateAd,
        
        // Универсальные методы
        updateAdStatus,
        
        // Массовые операции
        deleteMultipleAds,
        updateMultipleAdsStatus,
    }
}

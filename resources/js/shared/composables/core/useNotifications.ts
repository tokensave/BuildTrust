/**
 * 🔔 useNotifications - Централизованное управление уведомлениями
 * 
 * Предоставляет единый API для показа toast уведомлений во всем приложении.
 * Устраняет дублирование кода и обеспечивает консистентность UX.
 * 
 * @example
 * ```typescript
 * const { showSuccess, showError, showAdCreated } = useNotifications()
 * 
 * // Общие уведомления
 * showSuccess('Данные сохранены')
 * showError('Произошла ошибка')
 * 
 * // Предустановленные уведомления для частых случаев
 * showAdCreated()
 * showAdDeleted()
 * ```
 */

import { toast } from 'vue-sonner'

export function useNotifications() {
    // Базовые методы
    const showSuccess = (message: string, options = {}) => {
        toast.success(message, { 
            duration: 3000,
            ...options 
        })
    }

    const showError = (message: string, options = {}) => {
        toast.error(message, { 
            duration: 5000,
            ...options 
        })
    }

    const showInfo = (message: string, options = {}) => {
        toast.info(message, { 
            duration: 4000,
            ...options 
        })
    }

    const showWarning = (message: string, options = {}) => {
        toast.warning(message, { 
            duration: 4000,
            ...options 
        })
    }

    // Предустановленные уведомления для объявлений
    const showAdCreated = () => showSuccess('Объявление успешно создано')
    const showAdUpdated = () => showSuccess('Объявление обновлено')
    const showAdDeleted = () => showSuccess('Объявление удалено')
    const showAdPublished = () => showSuccess('Объявление опубликовано')
    const showAdArchived = () => showInfo('Объявление перемещено в архив')

    // Предустановленные уведомления для сделок
    const showDealCreated = () => showSuccess('Предложение отправлено')
    const showDealAccepted = () => showSuccess('Сделка принята')
    const showDealRejected = () => showWarning('Сделка отклонена')
    const showDealCompleted = () => showSuccess('Сделка завершена')
    const showDealCanceled = () => showInfo('Сделка отменена')

    // Уведомления об ошибках
    const showValidationError = (message = 'Проверьте правильность заполнения формы') => {
        showError(message)
    }
    
    const showNetworkError = (message = 'Ошибка соединения. Попробуйте позже') => {
        showError(message)
    }
    
    const showPermissionError = (message = 'У вас нет прав для выполнения этого действия') => {
        showError(message)
    }

    return {
        // Базовые методы
        showSuccess,
        showError,
        showInfo,
        showWarning,

        // Объявления
        showAdCreated,
        showAdUpdated,
        showAdDeleted,
        showAdPublished,
        showAdArchived,

        // Сделки
        showDealCreated,
        showDealAccepted,
        showDealRejected,
        showDealCompleted,
        showDealCanceled,

        // Ошибки
        showValidationError,
        showNetworkError,
        showPermissionError,
    }
}

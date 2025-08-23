/**
 * 💬 useConfirmDialog - Композабл для диалогов подтверждения
 * 
 * Предоставляет единообразный способ показа диалогов подтверждения
 * вместо нативных confirm() диалогов браузера.
 * 
 * @example
 * ```typescript
 * const { confirm } = useConfirmDialog()
 * 
 * const result = await confirm({
 *     title: 'Удалить объявление?',
 *     message: 'Это действие нельзя отменить',
 *     confirmText: 'Удалить',
 *     cancelText: 'Отмена'
 * })
 * 
 * if (result) {
 *     // Пользователь подтвердил
 * }
 * ```
 */

import { ref } from 'vue'

interface ConfirmOptions {
    title: string
    message: string
    confirmText?: string
    cancelText?: string
    type?: 'default' | 'danger' | 'warning'
}

interface ConfirmState {
    isOpen: boolean
    options: ConfirmOptions | null
    resolve: ((value: boolean) => void) | null
}

const state = ref<ConfirmState>({
    isOpen: false,
    options: null,
    resolve: null
})

export function useConfirmDialog() {
    // Показать диалог подтверждения (временная заглушка с нативным confirm)
    const confirm = (options: ConfirmOptions): Promise<boolean> => {
        return new Promise((resolve) => {
            // Временно используем нативный confirm, пока не создадим полноценный UI
            const message = `${options.title}\n\n${options.message}`
            const result = window.confirm(message)
            resolve(result)
        })
    }

    // Подтвердить действие
    const handleConfirm = () => {
        if (state.value.resolve) {
            state.value.resolve(true)
        }
        closeDialog()
    }

    // Отменить действие
    const handleCancel = () => {
        if (state.value.resolve) {
            state.value.resolve(false)
        }
        closeDialog()
    }

    // Закрыть диалог
    const closeDialog = () => {
        state.value = {
            isOpen: false,
            options: null,
            resolve: null
        }
    }

    return {
        // Состояние
        confirmState: state,

        // Методы
        confirm,
        handleConfirm,
        handleCancel,
        closeDialog
    }
}

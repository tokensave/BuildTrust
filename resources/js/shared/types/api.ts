/**
 * 🔗 API Types - Типы для работы с API
 * 
 * Общие типы для унификации работы с API ответами,
 * пагинацией и обработкой ошибок.
 */

// Базовый ответ API
export interface ApiResponse<T = any> {
    status: 'success' | 'error'
    message?: string
    data: T
}

// Пагинированный ответ
export interface PaginatedResponse<T> {
    data: T[]
    meta: {
        current_page: number
        last_page: number
        per_page: number
        total: number
        from: number | null
        to: number | null
        path: string
    }
    links: {
        first: string
        last: string
        prev: string | null
        next: string | null
    }
}

// Опция для select/enum
export interface EnumOption {
    value: string
    label: string
    description?: string
    color?: string
}

// Ошибки валидации
export interface ValidationErrors {
    [field: string]: string[]
}

// Ошибки API
export interface ApiError {
    message: string
    errors?: ValidationErrors
    status?: number
}

// Состояние загрузки
export interface LoadingState {
    loading: boolean
    error: string | null
}

// Фильтры с пагинацией
export interface FilterParams {
    page?: number
    per_page?: number
    sort?: string
    sort_direction?: 'asc' | 'desc'
    [key: string]: any
}

// Мета-информация для форм
export interface FormMeta {
    processing: boolean
    errors: ValidationErrors
    recentlySuccessful: boolean
    isDirty: boolean
}

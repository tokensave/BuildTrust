/**
 * 🎨 Formatting Utils - Утилиты для форматирования данных
 * 
 * Централизованные функции для форматирования цен, дат, текста и других данных.
 * Обеспечивает единообразие отображения по всему приложению.
 */

/**
 * Форматирование цены
 */
export const formatPrice = (price: number | null | undefined): string => {
    if (price === null || price === undefined || price === 0) {
        return 'Договорная'
    }
    
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(price)
}

/**
 * Форматирование даты
 */
export const formatDate = (date: string | Date, options: Intl.DateTimeFormatOptions = {}): string => {
    const defaultOptions: Intl.DateTimeFormatOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        ...options
    }
    
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return new Intl.DateTimeFormat('ru-RU', defaultOptions).format(dateObj)
}

/**
 * Форматирование даты и времени
 */
export const formatDateTime = (date: string | Date): string => {
    return formatDate(date, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

/**
 * Относительное время (например, "2 часа назад")
 */
export const formatRelativeTime = (date: string | Date): string => {
    const now = new Date()
    const dateObj = typeof date === 'string' ? new Date(date) : date
    const diffInSeconds = Math.floor((now.getTime() - dateObj.getTime()) / 1000)
    
    const intervals = [
        { label: 'год', seconds: 31536000 },
        { label: 'месяц', seconds: 2592000 },
        { label: 'день', seconds: 86400 },
        { label: 'час', seconds: 3600 },
        { label: 'минута', seconds: 60 }
    ]
    
    for (const interval of intervals) {
        const count = Math.floor(diffInSeconds / interval.seconds)
        if (count >= 1) {
            return `${count} ${getDeclension(count, interval.label)} назад`
        }
    }
    
    return 'только что'
}

/**
 * Склонение русских слов
 */
const getDeclension = (count: number, word: string): string => {
    const cases = {
        'год': ['год', 'года', 'лет'],
        'месяц': ['месяц', 'месяца', 'месяцев'],
        'день': ['день', 'дня', 'дней'],
        'час': ['час', 'часа', 'часов'],
        'минута': ['минута', 'минуты', 'минут']
    }
    
    const wordCases = cases[word as keyof typeof cases]
    if (!wordCases) return word
    
    const lastDigit = count % 10
    const lastTwoDigits = count % 100
    
    if (lastTwoDigits >= 11 && lastTwoDigits <= 19) {
        return wordCases[2]
    }
    
    if (lastDigit === 1) return wordCases[0]
    if (lastDigit >= 2 && lastDigit <= 4) return wordCases[1]
    return wordCases[2]
}

/**
 * Обрезание текста с троеточием
 */
export const truncateText = (text: string, maxLength: number = 100): string => {
    if (!text) return ''
    if (text.length <= maxLength) return text
    return text.slice(0, maxLength).trim() + '...'
}

/**
 * Форматирование размера файла
 */
export const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Б'
    
    const units = ['Б', 'КБ', 'МБ', 'ГБ']
    const unitIndex = Math.floor(Math.log(bytes) / Math.log(1024))
    const size = bytes / Math.pow(1024, unitIndex)
    
    return `${size.toFixed(1)} ${units[unitIndex]}`
}

/**
 * Форматирование номера телефона
 */
export const formatPhone = (phone: string): string => {
    // Удаляем все нецифровые символы
    const digits = phone.replace(/\D/g, '')
    
    // Если номер начинается с 8, заменяем на 7
    const normalizedDigits = digits.startsWith('8') ? '7' + digits.slice(1) : digits
    
    // Форматируем как +7 (XXX) XXX-XX-XX
    if (normalizedDigits.length === 11 && normalizedDigits.startsWith('7')) {
        return `+7 (${normalizedDigits.slice(1, 4)}) ${normalizedDigits.slice(4, 7)}-${normalizedDigits.slice(7, 9)}-${normalizedDigits.slice(9, 11)}`
    }
    
    return phone // Возвращаем как есть, если не удалось отформатировать
}

/**
 * Форматирование процентов
 */
export const formatPercent = (value: number, decimals: number = 1): string => {
    return `${value.toFixed(decimals)}%`
}

/**
 * Форматирование числа с разделителями тысяч
 */
export const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('ru-RU').format(value)
}

/**
 * Капитализация первой буквы
 */
export const capitalize = (text: string): string => {
    if (!text) return ''
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
}

/**
 * Форматирование множественного числа
 */
export const pluralize = (count: number, singular: string, few: string, many: string): string => {
    return `${count} ${getDeclension(count, singular)}`
}

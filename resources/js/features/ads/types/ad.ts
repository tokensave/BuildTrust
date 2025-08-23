/**
 * 📢 Ad Types - Типы для работы с объявлениями
 * 
 * Все типы, интерфейсы и энамы, связанные с объявлениями.
 * Синхронизированы с бэкенд моделями и API.
 */

// Основной интерфейс объявления
export interface Ad {
    id: number
    title: string
    type: AdType
    category?: AdCategory
    subcategory?: AdSubcategory
    location?: string
    description: string
    price: number | null
    is_urgent: boolean
    features?: string[]
    slug: string
    status: AdStatus
    user_id: number
    created_at: string
    updated_at: string
    
    // Аксессоры (из модели Laravel)
    image_url: string
    formatted_category: string
    is_service: boolean
    
    // Связанные модели
    media?: MediaFile[]
    user: {
        id: number
        username: string
        email: string
        company?: Company
    }
}

// Типы из enum'ов (значения приходят с бэкенда)
export type AdStatus = 'draft' | 'published' | 'archived'
export type AdType = 'goods' | 'services'
export type AdCategory = 'materials' | 'tools' | 'equipment' | 'construction' | 'repair' | 'design'
export type AdSubcategory = 
    // Materials
    | 'concrete' | 'brick' | 'wood_lumber' | 'metal_steel' 
    | 'insulation' | 'roofing' | 'tiles' | 'plumbing' 
    | 'electrical' | 'paint' | 'windows' | 'doors'
    // Tools  
    | 'hand_tools' | 'power_tools' | 'measuring_tools' | 'safety_equipment'
    // Equipment
    | 'heavy_machinery' | 'construction_equipment' | 'rental_equipment'
    // Construction
    | 'foundation' | 'walls_structure' | 'roofing_services' 
    | 'finishing_work' | 'landscaping'
    // Repair
    | 'apartment_renovation' | 'house_renovation' | 'bathroom_renovation'
    | 'kitchen_renovation' | 'office_renovation'
    // Design
    | 'architectural_design' | 'interior_design' 
    | 'engineering_design' | 'landscape_design'

// Фильтры для объявлений
export interface AdFilters {
    type?: AdType
    category?: AdCategory
    subcategory?: AdSubcategory
    location?: string
    min_price?: number
    max_price?: number
    urgent?: boolean
    search?: string
    features?: string[]
    status?: AdStatus // только для админки/личного кабинета
}

// Данные формы создания объявления
export interface CreateAdForm {
    title: string
    description: string
    type: AdType
    category?: AdCategory
    subcategory?: AdSubcategory
    price?: number
    location?: string
    features?: string[]
    images?: File[]
    is_urgent: boolean
    status: AdStatus
}

// Данные формы редактирования объявления
export interface UpdateAdForm extends CreateAdForm {
    deletedMediaIds?: number[] // ID изображений для удаления
    newImages?: File[]         // Новые изображения
}

// Медиа файлы
export interface MediaFile {
    id: number
    original_url: string
    name: string
    size: number
    mime_type: string
}

// Компания (связанная модель)
export interface Company {
    inn: string
    name: string
    email: string
    phone: string
    city: string
    address: string
    website?: string
    verified: boolean
}

// Статистика объявлений (для дашборда)
export interface AdStats {
    total: number
    published: number
    draft: number
    archived: number
    views?: number
    favorites?: number
}

// Варианты отображения карточек
export type AdCardVariant = 'grid' | 'list' | 'compact'

// Действия с объявлениями
export type AdAction = 'edit' | 'delete' | 'publish' | 'archive' | 'view' | 'duplicate'

// События компонентов
export interface AdEvents {
    'edit': (ad: Ad) => void
    'delete': (ad: Ad) => void
    'publish': (ad: Ad) => void
    'archive': (ad: Ad) => void
    'view': (ad: Ad) => void
}

// Типы объявлений
export const AD_TYPES = [
    {
        value: 'goods',
        label: 'Товары',
        description: 'Строительные материалы, инструменты, оборудование'
    },
    {
        value: 'services',
        label: 'Услуги',
        description: 'Строительные и ремонтные работы, консультации, проектирование'
    }
] as const;

// Категории для товаров
export const GOODS_CATEGORIES = [
    { value: 'materials', label: 'Материалы' },
    { value: 'tools', label: 'Инструменты' },
    { value: 'equipment', label: 'Оборудование' }
] as const;

// Категории для услуг
export const SERVICES_CATEGORIES = [
    { value: 'construction', label: 'Строительство' },
    { value: 'repair', label: 'Ремонт' },
    { value: 'design', label: 'Проектирование' }
] as const;

// Все категории
export const ALL_CATEGORIES = [...GOODS_CATEGORIES, ...SERVICES_CATEGORIES] as const;

// Подкатегории материалов
export const MATERIALS_SUBCATEGORIES = [
    { value: 'concrete', label: 'Бетон и железобетон' },
    { value: 'brick', label: 'Кирпич и блоки' },
    { value: 'wood_lumber', label: 'Пиломатериалы' },
    { value: 'metal_steel', label: 'Металлопрокат' },
    { value: 'insulation', label: 'Утеплители' },
    { value: 'roofing', label: 'Кровельные материалы' },
    { value: 'tiles', label: 'Плитка и керамика' },
    { value: 'plumbing', label: 'Сантехника' },
    { value: 'electrical', label: 'Электротовары' },
    { value: 'paint', label: 'Лакокрасочные материалы' },
    { value: 'windows', label: 'Окна' },
    { value: 'doors', label: 'Двери' }
] as const;

// Подкатегории инструментов
export const TOOLS_SUBCATEGORIES = [
    { value: 'hand_tools', label: 'Ручной инструмент' },
    { value: 'power_tools', label: 'Электроинструмент' },
    { value: 'measuring_tools', label: 'Измерительный инструмент' },
    { value: 'safety_equipment', label: 'Средства защиты' }
] as const;

// Подкатегории оборудования
export const EQUIPMENT_SUBCATEGORIES = [
    { value: 'heavy_machinery', label: 'Тяжелая техника' },
    { value: 'construction_equipment', label: 'Строительное оборудование' },
    { value: 'rental_equipment', label: 'Оборудование в аренду' }
] as const;

// Подкатегории строительства
export const CONSTRUCTION_SUBCATEGORIES = [
    { value: 'foundation', label: 'Фундаментные работы' },
    { value: 'walls_structure', label: 'Возведение стен' },
    { value: 'roofing_services', label: 'Кровельные работы' },
    { value: 'finishing_work', label: 'Отделочные работы' },
    { value: 'landscaping', label: 'Благоустройство' }
] as const;

// Подкатегории ремонта
export const REPAIR_SUBCATEGORIES = [
    { value: 'apartment_renovation', label: 'Ремонт квартир' },
    { value: 'house_renovation', label: 'Ремонт домов' },
    { value: 'bathroom_renovation', label: 'Ремонт ванных' },
    { value: 'kitchen_renovation', label: 'Ремонт кухонь' },
    { value: 'office_renovation', label: 'Ремонт офисов' }
] as const;

// Подкатегории проектирования
export const DESIGN_SUBCATEGORIES = [
    { value: 'architectural_design', label: 'Архитектурное проектирование' },
    { value: 'interior_design', label: 'Дизайн интерьера' },
    { value: 'engineering_design', label: 'Инженерное проектирование' },
    { value: 'landscape_design', label: 'Ландшафтный дизайн' }
] as const;

// Все подкатегории сгруппированные
export const SUBCATEGORIES_BY_CATEGORY = {
    materials: MATERIALS_SUBCATEGORIES,
    tools: TOOLS_SUBCATEGORIES,
    equipment: EQUIPMENT_SUBCATEGORIES,
    construction: CONSTRUCTION_SUBCATEGORIES,
    repair: REPAIR_SUBCATEGORIES,
    design: DESIGN_SUBCATEGORIES
} as const;

// Статусы объявлений (соответствуют PHP enum)
export const AD_STATUS_OPTIONS = [
    { value: 'draft', label: 'Черновик' },
    { value: 'published', label: 'Опубликовано' },
    { value: 'archived', label: 'Архив' }
] as const;

// Типы для TypeScript
export type AdTypeValue = typeof AD_TYPES[number]['value'];
export type AdCategoryValue = typeof ALL_CATEGORIES[number]['value'];
export type AdStatusValue = typeof AD_STATUS_OPTIONS[number]['value'];
export type AdSubcategoryValue =
    | typeof MATERIALS_SUBCATEGORIES[number]['value']
    | typeof TOOLS_SUBCATEGORIES[number]['value']
    | typeof EQUIPMENT_SUBCATEGORIES[number]['value']
    | typeof CONSTRUCTION_SUBCATEGORIES[number]['value']
    | typeof REPAIR_SUBCATEGORIES[number]['value']
    | typeof DESIGN_SUBCATEGORIES[number]['value'];

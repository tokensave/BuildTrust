<?php

namespace App\Enums\AdEnums;

enum AdSubcategoryEnum: string
{
    // Materials - Материалы
    case CONCRETE = 'concrete';
    case BRICK = 'brick';
    case WOOD_LUMBER = 'wood_lumber';
    case METAL_STEEL = 'metal_steel';
    case INSULATION = 'insulation';
    case ROOFING = 'roofing';
    case TILES = 'tiles';
    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case PAINT = 'paint';
    case WINDOWS = 'windows';
    case DOORS = 'doors';

    // Tools - Инструменты
    case HAND_TOOLS = 'hand_tools';
    case POWER_TOOLS = 'power_tools';
    case MEASURING_TOOLS = 'measuring_tools';
    case SAFETY_EQUIPMENT = 'safety_equipment';

    // Equipment - Оборудование
    case HEAVY_MACHINERY = 'heavy_machinery';
    case CONSTRUCTION_EQUIPMENT = 'construction_equipment';
    case RENTAL_EQUIPMENT = 'rental_equipment';

    // Construction - Строительство
    case FOUNDATION = 'foundation';
    case WALLS_STRUCTURE = 'walls_structure';
    case ROOFING_SERVICES = 'roofing_services';
    case FINISHING_WORK = 'finishing_work';
    case LANDSCAPING = 'landscaping';

    // Repair - Ремонт
    case APARTMENT_RENOVATION = 'apartment_renovation';
    case HOUSE_RENOVATION = 'house_renovation';
    case BATHROOM_RENOVATION = 'bathroom_renovation';
    case KITCHEN_RENOVATION = 'kitchen_renovation';
    case OFFICE_RENOVATION = 'office_renovation';

    // Design - Проектирование
    case ARCHITECTURAL_DESIGN = 'architectural_design';
    case INTERIOR_DESIGN = 'interior_design';
    case ENGINEERING_DESIGN = 'engineering_design';
    case LANDSCAPE_DESIGN = 'landscape_design';

    public function label(): string
    {
        return match ($this) {
            // Материалы
            self::CONCRETE => 'Бетон и железобетон',
            self::BRICK => 'Кирпич и блоки',
            self::WOOD_LUMBER => 'Пиломатериалы',
            self::METAL_STEEL => 'Металлопрокат',
            self::INSULATION => 'Утеплители',
            self::ROOFING => 'Кровельные материалы',
            self::TILES => 'Плитка и керамика',
            self::PLUMBING => 'Сантехника',
            self::ELECTRICAL => 'Электротовары',
            self::PAINT => 'Лакокрасочные материалы',
            self::WINDOWS => 'Окна',
            self::DOORS => 'Двери',
            
            // Инструменты
            self::HAND_TOOLS => 'Ручной инструмент',
            self::POWER_TOOLS => 'Электроинструмент',
            self::MEASURING_TOOLS => 'Измерительный инструмент',
            self::SAFETY_EQUIPMENT => 'Средства защиты',
            
            // Оборудование
            self::HEAVY_MACHINERY => 'Тяжелая техника',
            self::CONSTRUCTION_EQUIPMENT => 'Строительное оборудование',
            self::RENTAL_EQUIPMENT => 'Оборудование в аренду',
            
            // Строительство
            self::FOUNDATION => 'Фундаментные работы',
            self::WALLS_STRUCTURE => 'Возведение стен',
            self::ROOFING_SERVICES => 'Кровельные работы',
            self::FINISHING_WORK => 'Отделочные работы',
            self::LANDSCAPING => 'Благоустройство',
            
            // Ремонт
            self::APARTMENT_RENOVATION => 'Ремонт квартир',
            self::HOUSE_RENOVATION => 'Ремонт домов',
            self::BATHROOM_RENOVATION => 'Ремонт ванных',
            self::KITCHEN_RENOVATION => 'Ремонт кухонь',
            self::OFFICE_RENOVATION => 'Ремонт офисов',
            
            // Проектирование
            self::ARCHITECTURAL_DESIGN => 'Архитектурное проектирование',
            self::INTERIOR_DESIGN => 'Дизайн интерьера',
            self::ENGINEERING_DESIGN => 'Инженерное проектирование',
            self::LANDSCAPE_DESIGN => 'Ландшафтный дизайн',
        };
    }

    /**
     * Получить родительскую категорию
     */
    public function getCategory(): AdCategoryEnum
    {
        return match ($this) {
            self::CONCRETE, self::BRICK, self::WOOD_LUMBER, self::METAL_STEEL,
            self::INSULATION, self::ROOFING, self::TILES, self::PLUMBING,
            self::ELECTRICAL, self::PAINT, self::WINDOWS, self::DOORS
                => AdCategoryEnum::MATERIALS,

            self::HAND_TOOLS, self::POWER_TOOLS, self::MEASURING_TOOLS, self::SAFETY_EQUIPMENT
                => AdCategoryEnum::TOOLS,

            self::HEAVY_MACHINERY, self::CONSTRUCTION_EQUIPMENT, self::RENTAL_EQUIPMENT
                => AdCategoryEnum::EQUIPMENT,

            self::FOUNDATION, self::WALLS_STRUCTURE, self::ROOFING_SERVICES,
            self::FINISHING_WORK, self::LANDSCAPING
                => AdCategoryEnum::CONSTRUCTION,

            self::APARTMENT_RENOVATION, self::HOUSE_RENOVATION, self::BATHROOM_RENOVATION,
            self::KITCHEN_RENOVATION, self::OFFICE_RENOVATION
                => AdCategoryEnum::REPAIR,

            self::ARCHITECTURAL_DESIGN, self::INTERIOR_DESIGN,
            self::ENGINEERING_DESIGN, self::LANDSCAPE_DESIGN
                => AdCategoryEnum::DESIGN,
        };
    }

    /**
     * Получить подкатегории по категории
     */
    public static function getByCategory(AdCategoryEnum $category): array
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->getCategory() === $category)
            ->values()
            ->toArray();
    }

    /**
     * Получить все подкатегории для фронтенда, сгруппированные по категориям
     */
    public static function toArrayGrouped(): array
    {
        return collect(AdCategoryEnum::cases())
            ->mapWithKeys(function($category) {
                $subcategories = self::getByCategory($category);
                return [
                    $category->value => [
                        'label' => $category->label(),
                        'subcategories' => collect($subcategories)->mapWithKeys(fn($sub) => [
                            $sub->value => [
                                'value' => $sub->value,
                                'label' => $sub->label(),
                            ]
                        ])->toArray()
                    ]
                ];
            })
            ->toArray();
    }
}

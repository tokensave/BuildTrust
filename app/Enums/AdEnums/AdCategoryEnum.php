<?php

namespace App\Enums\AdEnums;

enum AdCategoryEnum: string
{
    // Goods
    case MATERIALS = 'materials';
    case TOOLS = 'tools';
    case EQUIPMENT = 'equipment';

    // Services
    case CONSTRUCTION = 'construction';
    case REPAIR = 'repair';
    case DESIGN = 'design';

    public function label(): string
    {
        return match ($this) {
            self::MATERIALS => 'Материалы',
            self::TOOLS => 'Инструменты',
            self::EQUIPMENT => 'Оборудование',
            self::CONSTRUCTION => 'Строительство',
            self::REPAIR => 'Ремонт',
            self::DESIGN => 'Проектирование',
        };
    }

    /**
     * Категории, относящиеся к товарам
     */
    public static function goods(): array
    {
        return [self::MATERIALS, self::TOOLS, self::EQUIPMENT];
    }

    /**
     * Категории, относящиеся к услугам
     */
    public static function services(): array
    {
        return [self::CONSTRUCTION, self::REPAIR, self::DESIGN];
    }

    /**
     * Получить категории по типу
     */
    public static function getByType(AdTypeEnum $type): array
    {
        return match ($type) {
            AdTypeEnum::GOODS => self::goods(),
            AdTypeEnum::SERVICES => self::services(),
        };
    }

    /**
     * Получить все категории для фронтенда
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ]
            ])
            ->toArray();
    }
}

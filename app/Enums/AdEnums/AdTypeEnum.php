<?php

namespace App\Enums\AdEnums;

enum AdTypeEnum: string
{
    case GOODS = 'goods';
    case SERVICES = 'services';

    public function label(): string
    {
        return match ($this) {
            self::GOODS => 'Товары',
            self::SERVICES => 'Услуги'
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GOODS => 'Строительные материалы, инструменты, оборудование',
            self::SERVICES => 'Строительные и ремонтные работы, консультации, проектирование'
        };
    }

    /**
     * Получить все типы для фронтенда
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'description' => $case->description()
                ]
            ])
            ->toArray();
    }
}

<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Enums\AdEnums;

enum AdsStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::PUBLISHED => 'Опубликовано',
            self::ARCHIVED => 'Архив',
        };
    }

    /**
     * Получить все статусы для фронтенда
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'value' => $case->value,
                    'label' => $case->label()
                ]
            ])
            ->toArray();
    }
}

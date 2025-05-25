<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Enums\DealEnums;

enum DealStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case  REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'В ожидании',
            self::ACCEPTED => 'Принята',
            self::REJECTED => 'Отклонена',
            self::COMPLETED => 'Завершена',
            self::CANCELED => 'Отменена',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::ACCEPTED => 'bg-blue-100 text-blue-800',
            self::REJECTED => 'bg-gray-200 text-gray-700',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CANCELED => 'bg-red-100 text-red-800',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Deal\ValueObjects;

/**
 * Статус сделки с бизнес-логикой переходов
 * Перенесено из Enums/DealEnums/DealStatusEnum.php в правильное место
 */
enum DealStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted'; 
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    /**
     * Человеко-читаемые названия для UI
     */
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

    /**
     * CSS классы для раскраски в UI
     */
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

    // === БИЗНЕС-ЛОГИКА ПЕРЕХОДОВ ===

    /**
     * Можно ли подтвердить сделку
     */
    public function canBeAccepted(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Можно ли отклонить сделку
     */
    public function canBeRejected(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Можно ли завершить сделку
     */
    public function canBeCompleted(): bool
    {
        return $this === self::ACCEPTED;
    }

    /**
     * Можно ли отменить сделку
     */
    public function canBeCanceled(): bool
    {
        return in_array($this, [self::PENDING, self::ACCEPTED]);
    }

    /**
     * Проверка валидного перехода между статусами
     */
    public function canTransitionTo(DealStatus $newStatus): bool
    {
        // Финальные статусы нельзя изменить
        if ($this->isFinal()) {
            return false;
        }
        
        return match ([$this, $newStatus]) {
            // Из PENDING можно в любой другой статус
            [self::PENDING, self::ACCEPTED] => true,
            [self::PENDING, self::REJECTED] => true,
            [self::PENDING, self::CANCELED] => true,
            
            // Из ACCEPTED можно завершить или отменить
            [self::ACCEPTED, self::COMPLETED] => true,
            [self::ACCEPTED, self::CANCELED] => true,
            
            // Все остальные переходы недопустимы
            default => false,
        };
    }

    /**
     * Получить допустимые следующие статусы
     */
    public function getAvailableTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::ACCEPTED, self::REJECTED, self::CANCELED],
            self::ACCEPTED => [self::COMPLETED, self::CANCELED],
            self::REJECTED => [],
            self::COMPLETED => [],
            self::CANCELED => [],
        };
    }

    /**
     * Проверить, является ли статус финальным
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::REJECTED, self::COMPLETED, self::CANCELED]);
    }

    /**
     * Проверить, является ли статус активным (сделка в процессе)
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::ACCEPTED]);
    }
}

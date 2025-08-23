<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects\Ids;

/**
 * Типобезопасный идентификатор сделки
 * Предотвращает случайную передачу ID другой сущности
 */
final readonly class DealId
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Deal ID должен быть положительным числом');
        }

        $this->value = $value;
    }

    /**
     * Создать из целого числа
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    /**
     * Получить числовое значение (для БД и API)
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Получить строковое представление
     */
    public function toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Сравнение ID
     */
    public function equals(DealId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

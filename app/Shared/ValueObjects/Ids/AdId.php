<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects\Ids;

/**
 * Типобезопасный идентификатор объявления
 */
final readonly class AdId
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Ad ID должен быть положительным числом');
        }

        $this->value = $value;
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function equals(AdId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects\Ids;

/**
 * Типобезопасный идентификатор пользователя
 */
final readonly class UserId
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('User ID должен быть положительным числом');
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

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

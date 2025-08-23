<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\InvalidMoneyException;

/**
 * Value Object для работы с деньгами
 * Использует копейки внутри для избежания проблем с float
 */
final class Money
{
    private readonly int $amountInKopecks;
    private readonly string $currency;

    private function __construct(int $amountInKopecks, string $currency = 'RUB')
    {
        if ($amountInKopecks < 0) {
            throw new InvalidMoneyException('Сумма не может быть отрицательной');
        }
        
        $this->amountInKopecks = $amountInKopecks;
        $this->currency = $currency;
    }

    /**
     * Создать из рублей (основной способ создания)
     */
    public static function fromRubles(float $rubles): self
    {
        if ($rubles < 0) {
            throw new InvalidMoneyException('Сумма в рублях не может быть отрицательной');
        }
        
        return new self((int) round($rubles * 100));
    }

    /**
     * Создать из копеек (для внутренних нужд)
     */
    public static function fromKopecks(int $kopecks): self
    {
        return new self($kopecks);
    }

    /**
     * Получить сумму в рублях
     */
    public function toRubles(): float
    {
        return $this->amountInKopecks / 100;
    }

    /**
     * Получить сумму в копейках (для БД и расчетов)
     */
    public function toKopecks(): int
    {
        return $this->amountInKopecks;
    }

    /**
     * Форматированный вывод для пользователя
     */
    public function format(): string
    {
        return number_format($this->toRubles(), 2, '.', ' ') . ' ₽';
    }

    /**
     * Сравнение денег
     */
    public function equals(Money $other): bool
    {
        return $this->amountInKopecks === $other->amountInKopecks 
            && $this->currency === $other->currency;
    }

    /**
     * Больше чем
     */
    public function greaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amountInKopecks > $other->amountInKopecks;
    }

    /**
     * Меньше чем
     */
    public function lessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amountInKopecks < $other->amountInKopecks;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidMoneyException(
                "Нельзя сравнивать деньги разных валют: {$this->currency} и {$other->currency}"
            );
        }
    }
}

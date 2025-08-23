<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use InvalidArgumentException;

/**
 * Исключение для некорректных денежных операций
 */
final class InvalidMoneyException extends InvalidArgumentException
{
    public static function negativeAmount(): self
    {
        return new self('Сумма не может быть отрицательной');
    }
    
    public static function differentCurrencies(string $currency1, string $currency2): self
    {
        return new self("Нельзя выполнять операции с разными валютами: {$currency1} и {$currency2}");
    }
}

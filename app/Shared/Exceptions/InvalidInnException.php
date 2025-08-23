<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use InvalidArgumentException;

/**
 * Исключение для неверного формата ИНН
 */
final class InvalidInnException extends InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self("Неверный формат ИНН: {$value}");
    }
}
